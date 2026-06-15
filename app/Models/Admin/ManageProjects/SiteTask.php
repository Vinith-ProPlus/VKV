<?php

namespace App\Models\Admin\ManageProjects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'stage_id',
        'name',
        'date',
        'image',
        'description',
        'status',
        'created_by_id',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function stage()
    {
        return $this->belongsTo(SiteStage::class, 'stage_id');
    }

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
