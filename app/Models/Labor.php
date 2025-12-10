<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Labor\SiteLaborDate;
use App\Models\Admin\Labor\LaborDesignation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $only)
 * @method static find($id)
 * @method static where(string $string, $value)
 */
class Labor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['site_labor_date_id', 'name', 'mobile', 'salary', 'labor_designation_id', 'paid_status'];

    /**
     * @return BelongsTo
     */
    public function siteLaborDate(): BelongsTo
    {
        return $this->belongsTo(SiteLaborDate::class);
    }
    public function labor_designation(): BelongsTo
    {
        return $this->belongsTo(LaborDesignation::class);
    }

    public function payrolls() {
        return $this->hasMany(Payroll::class);
    }
}
