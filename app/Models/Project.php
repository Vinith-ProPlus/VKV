<?php

namespace App\Models;

use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\ProjectContract;
use App\Models\ProjectAmenity;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'project_id',
        'name',
        'location',
        'type',
        'units',
        'target_customers',
        'range',
        'engineer_id',
        'area_sqft',
        'status',
        'investment_amount',
        'sold_amount',
    ];

    protected $appends = ['completion_percentage'];

    public function stages(): HasMany
    {
        return $this->HasMany(ProjectStage::class);
    }

    public function contracts(): HasMany
    {
        return $this->HasMany(ProjectContract::class);
    }

    public function amenities(): HasMany
    {
        return $this->HasMany(ProjectAmenity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }
    public function site(): BelongsToAlias
    {
        return $this->belongsTo(Site::class);
    }
    /**
     * @return BelongsToAlias
     */
    public function engineer(): BelongsToAlias
    {
        return $this->BelongsTo(User::class);
    }
    public function getCompletionPercentageAttribute(): string
    {
        $totalTasks = $this->tasks()->whereIn('status', ['Created', 'In-progress', 'Completed'])->count();
        $completedTasks = $this->tasks()->where('status', 'Completed')->count();

        return ($totalTasks === 0 ? 0.0 : round(($completedTasks / $totalTasks) * 100, 2))."%";
    }

}

