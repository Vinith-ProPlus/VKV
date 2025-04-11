<?php

namespace App\Models;

use App\Models\Admin\Labor\ProjectLaborDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborReallocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'labor_id',
        'from_project_labor_date_id',
        'to_project_labor_date_id',
        'remarks',
        'reallocated_by',
    ];

    public function labor(): BelongsTo
    {
        return $this->belongsTo(Labor::class);
    }

    public function fromProjectLaborDate(): BelongsTo
    {
        return $this->belongsTo(ProjectLaborDate::class, 'from_project_labor_date_id');
    }

    public function toProjectLaborDate(): BelongsTo
    {
        return $this->belongsTo(ProjectLaborDate::class, 'to_project_labor_date_id');
    }

    public function reallocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reallocated_by');
    }
}
