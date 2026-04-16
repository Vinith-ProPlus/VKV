<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $all)
 * @method static findOrFail($id)
 */
class SiteStage extends Model
{
    use SoftDeletes;

    protected $fillable = ['site_id', 'name', 'order_no'    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function tasks()
    {
        return $this->hasMany(SiteTask::class, 'stage_id');
    }
}
