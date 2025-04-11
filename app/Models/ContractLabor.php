<?php

namespace App\Models;

use App\Models\Admin\Labor\ProjectLaborDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $only)
 * @method static findOrFail($id)
 */
class ContractLabor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_labor_date_id', 'project_contract_id', 'count'];

    /**
     * @return BelongsTo
     */
    public function projectLaborDate(): BelongsTo
    {
        return $this->belongsTo(ProjectLaborDate::class);
    }

    /**
     * @return BelongsTo
     */
    public function projectContract(): BelongsTo
    {
        return $this->belongsTo(ProjectContract::class, 'project_contract_id');
    }
}

