<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $id)
 * @method static create(array $array)
 */
class MobileUserAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'user_device_id',
        'user_device_location_id',
        'time',
    ];

    /**
     * Relationship: MobileUserAttendance belongs to UserDevice
     */
    public function userDevice(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'user_device_id');
    }

    /**
     * Relationship: MobileUserAttendance belongs to UserDeviceLocation
     */
    public function userDeviceLocation(): BelongsTo
    {
        return $this->belongsTo(UserDeviceLocation::class, 'user_device_location_id');
    }
}
