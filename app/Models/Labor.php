<?php

namespace App\Models;

use App\Models\Admin\Labor\LaborDesignation;
use App\Models\Admin\Labor\ProjectLaborDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $only)
 */
class Labor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_labor_date_id', 'name', 'mobile', 'salary', 'labor_designation_id', 'paid_status'];

    /**
     * @return BelongsTo
     */
    public function projectLaborDate(): BelongsTo
    {
        return $this->belongsTo(ProjectLaborDate::class);
    }
    public function labor_designation(): BelongsTo
    {
        return $this->belongsTo(LaborDesignation::class);
    }

    public function payrolls() {
        return $this->hasMany(Payroll::class);
    }
}
