<?php

namespace App\Models;

use App\Models\Admin\Master\City;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @method static orderByDesc(string $string)
 */
class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'address',
        'state_id',
        'district_id',
        'city_id',
        'pincode_id',
        'is_active'
    ];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('is_active', 1);
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * @return BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsTo
     */
    public function pincode(): BelongsTo
    {
        return $this->belongsTo(Pincode::class);
    }
}
