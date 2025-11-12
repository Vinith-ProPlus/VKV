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
}
