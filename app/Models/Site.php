<?php

namespace App\Models;

use App\Models\SiteContract;
use App\Models\Admin\ManageProjects\SiteStage;
use App\Models\Admin\ManageProjects\SiteTask;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 * @method static find(mixed $from_project_id)
 */
class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_no',
        'project_id',
        'type',
        'units',
        'range',
        'engineer_id',
        'area_sqft',
        'investment_amount',
        'sold_amount',
        'status',
    ];

    protected $appends = ['completion_percentage'];

    public function stages(): HasMany
    {
        return $this->HasMany(SiteStage::class);
    }

    public function contracts(): HasMany
    {
    return $this->HasMany(SiteContract::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SiteTask::class);
    }
    public function project(): BelongsToAlias
    {
        return $this->belongsTo(Project::class);
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

