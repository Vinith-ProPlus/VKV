<?php

namespace App\Models\Admin\Labor;

use App\Models\ContractLabor;
use App\Models\Labor;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static updateOrCreate(array $array)
 * @method static firstOrCreate(array $array)
 * @method static where(string $string, mixed $siteId)
 */
class SiteLaborDate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['site_id', 'date'];

    /**
     * @return BelongsTo
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany
     */
    public function labors(): HasMany
    {
        return $this->hasMany(Labor::class);
    }

    /**
     * @return HasMany
     */
    public function contractLabors(): HasMany
    {
        return $this->hasMany(ContractLabor::class);
    }

    /**
     * @return int
     */
    public function getLaborCountAttribute()
    {
        return $this->labors()->count();
    }

    /**
     * @return int|mixed
     */
    public function getContractLaborCountAttribute()
    {
        return $this->contractLabors()->sum('count');
    }
}
