<?php

namespace App\Models;

use App\Models\Admin\Labor\SiteLaborDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $only)
 * @method static findOrFail($id)
 * @method static where(string $string, $value)
 */
class ContractLabor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['site_labor_date_id', 'site_contract_id', 'count'];

    /**
     * @return BelongsTo
     */
    public function projectLaborDate(): BelongsTo
    {
        return $this->belongsTo(SiteLaborDate::class);
    }

    /**
     * @return BelongsTo
     */
    public function siteContract(): BelongsTo
    {
        return $this->belongsTo(SiteContract::class, 'site_contract_id');
    }

    /** @deprecated Use siteContract() */
    public function projectContract(): BelongsTo
    {
        return $this->siteContract();
    }
}

