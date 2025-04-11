<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\MobileUserAttendance;
use Carbon\Carbon;

class MobileUserAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(['check_in', 'check_out']),
                function ($attribute, $value, $fail) {
                    $this->validateCheckInCheckOutOrder($value, $fail);
                },
            ],
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'device_id' => ['required', 'string',
                Rule::exists('user_devices', 'device_id')->where(static function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
        ];
    }

    /**
     * Validate the check-in/check-out order.
     */
    private function validateCheckInCheckOutOrder($type, $fail): void
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Get the last attendance entry for the user today
        $lastAttendance = MobileUserAttendance::where('user_id', $userId)
            ->whereDate('time', $today)
            ->latest('time')
            ->first();

        if ($type === 'check_out') {
            // Restrict check-out if the last entry is not a check-in
            if (!$lastAttendance || $lastAttendance->type === 'check_out') {
                $fail("You must check-in before checking out.");
            }
        } else if ($type === 'check_in' && $lastAttendance && $lastAttendance->type === 'check_in') {
            $fail("You must check-out before checking out.");
        }
    }
}
