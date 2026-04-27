<?php

namespace App\Models;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'location', 'latitude', 'longitude', 'is_active'];
    protected $appends = ['completion_percentage'];

    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'site_supervisor', 'project_id', 'supervisor_id');
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
    
    public function amenities()
    {
        return $this->HasMany(ProjectAmenity::class);
    }

    /**
     * Calculate completion percentage for the entire project
     * by aggregating all tasks across all sites and their stages
     */
    public function getCompletionPercentageAttribute(): string
    {
        $totalTasks = 0;
        $completedTasks = 0;

        // Get all sites for this project
        $sites = $this->sites()->get();

        foreach ($sites as $site) {
            // Get all stages for each site
            $stages = $site->stages()->get();
            
            foreach ($stages as $stage) {
                // Get all tasks for each stage
                $stageTasks = $stage->tasks()
                    ->whereIn('status', ['Created', 'In-progress', 'Completed'])
                    ->get();

                $totalTasks += $stageTasks->count();
                $completedTasks += $stageTasks->where('status', 'Completed')->count();
            }
        }

        return ($totalTasks === 0 ? 0.0 : round(($completedTasks / $totalTasks) * 100, 2))."%";
    }
}