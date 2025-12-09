<?php

namespace App\Models\Admin\ManageProjects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteTask extends Model
{
    use SoftDeletes;

    protected $fillable = ['site_id', 'stage_id', 'name', 'order_no', 'status', 'start_date', 'end_date'];

    public function stage()
    {
        return $this->belongsTo(SiteStage::class, 'stage_id');
    }

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
