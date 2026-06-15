<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProjectTask extends SiteTask
{
    protected $table = 'site_tasks';

    public function project()
    {
        return $this->hasOneThrough(
            Project::class,
            \App\Models\Site::class,
            'id',
            'id',
            'site_id',
            'project_id'
        );
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeForSupervisor(Builder $query, int $userId): Builder
    {
        return $query->whereHas(
            'site.project.supervisors',
            static fn(Builder $q) => $q->where('users.id', $userId)
        );
    }

    public function scopeForProject(Builder $query, int $projectId): Builder
    {
        return $query->whereHas(
            'site',
            static fn(Builder $q) => $q->where('project_id', $projectId)
        );
    }
}
