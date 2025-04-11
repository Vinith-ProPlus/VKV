<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * @param int $user_id
     * @param string $title
     * @param string $body
     * @param string|null $module_name
     * @param int|null $module_id
     * @return array|string[]
     * @throws ConnectionException
     * @throws \JsonException
     */
    public static function sendPushNotification(int $user_id, string $title, string $body, string|null $module_name = null, int|null $module_id = null): array
    {
        $user = User::find($user_id);

        if (!$user) {
            return ['error' => 'User not found.'];
        }

        $devices = $user->devices()->get(['device_id', 'fcm_token'])->filter(static fn($device) => !empty($device->fcm_token));

        if ($devices->isEmpty()) {
            return ['error' => 'User does not have a valid FCM token.'];
        }

        $serviceAccountPath = storage_path('app/firebase/rdf_firebase_credentials.json');
        $projectId = config('app.FIREBASE_PROJECT_ID');

        $accessToken = generateFirebaseAccessToken($serviceAccountPath);
        if (!$accessToken) {
            return ['error' => 'Failed to generate access token.'];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $hasFailure = false;
        $validDeviceIds = [];
        $validFcmTokens = [];
        $status = 'failed';

        foreach ($devices as $device) {
            $payload = [
                'message' => [
                    'token' => $device->fcm_token,
                    'notification' => compact('title', 'body'),
                ],
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);
                if ($response->successful()) {
                    $validDeviceIds[] = $device->device_id;
                    $validFcmTokens[] = $device->fcm_token;
                } else {
                    $hasFailure = true;
                    Log::warning("PushNotification not sent for user '{$user->name}', device_id '{$device->device_id}', FCM token '{$device->fcm_token}' - Error: " . $response->body());
                }
            } catch (ConnectionException $e) {
                Log::error("Error in Notification Service@sendPushNotification: " . $e->getMessage());
            }
        }

        if ($devices->count()) {
            $status = $hasFailure ? 'partially failed' : 'sent';
        }
        Notification::create([
            'user_id'      => $user->id,
            'title'        => $title,
            'message'      => $body,
            'module_name'  => $module_name,
            'module_id'    => $module_id,
            'device_ids'   => json_encode($validDeviceIds, JSON_THROW_ON_ERROR),
            'fcm_tokens'   => json_encode($validFcmTokens, JSON_THROW_ON_ERROR),
            'status'       => $status,
        ]);

        return [
            'status' => $status,
            'sent_tokens' => $validFcmTokens,
            'sent_device_ids' => $validDeviceIds,
        ];
    }

}
