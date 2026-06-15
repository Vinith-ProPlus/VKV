<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProjectTask extends SiteTask
{
    protected $table = 'site_tasks';

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeWithApiRelations(Builder $query): Builder
    {
        return $query->with([
            'site:id,project_id,site_no',
            'site.project:id,name',
            'stage:id,site_id,name',
            'created_by:id,name',
        ]);
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
