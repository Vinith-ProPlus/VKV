<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MobileUserAttendanceRequest;
use App\Models\MobileUserAttendance;
use App\Models\UserDevice;
use App\Models\UserDeviceLocation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MobileUserAttendanceController extends Controller
{
    use ApiResponse;
    /**
     * Record User Check-in/Check-out
     */
    public function recordAttendance(MobileUserAttendanceRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::id();
            $device = UserDevice::where('user_id', $user_id)->where('device_id', $request->device_id)->first();

            $user_device_location = UserDeviceLocation::create([
                'user_id' => $user_id,
                'user_device_id' => $device->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            $attendance = MobileUserAttendance::create([
                'user_id' => Auth::id(),
                'user_device_id' => $device->id,
                'user_device_location_id' => $user_device_location->id,
                'ip_address' => $request->ip(),
                'time' => now(),
                'type' => $request->type,
            ]);

            DB::commit();
            return $this->successResponse($attendance, ucfirst($request->type) . " recorded successfully!");
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Error::AttendanceController@recordAttendance - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to record attendance!", 500);
        }
    }

    /**
     * Get User's Attendance History
     */
    public function getAttendanceHistory(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = MobileUserAttendance::where('user_id', $user->id)->with(['userDevice', 'userDeviceLocation']);
            $query = dataFilter($query, $request);
            return $this->successResponse(dataFormatter($query), "Attendance history fetched successfully!");
        } catch (Throwable $exception) {
            Log::error('Error::AttendanceController@getAttendanceHistory - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to fetch attendance history!", 500);
        }
    }
}
