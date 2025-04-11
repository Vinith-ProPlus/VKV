<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 */
class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'location', 'latitude', 'longitude', 'is_active'];

    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'site_supervisor', 'site_id', 'supervisor_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
