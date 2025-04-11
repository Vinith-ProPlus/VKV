<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $all)
 * @method static findOrFail($id)
 */
class ProjectStage extends Model
{
    use SoftDeletes;

    protected $fillable = ['project_id', 'name', 'order_no'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'stage_id');
    }
}
