<?php

namespace App\Models;

use App\Models\Admin\Master\Area;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'state_id',
        'district_id',
        'area_id',
        'pincode_id',
        'email',
        'mobile_number',
        'lead_source_id',
        'image',
    ];

    public function state() {
        return $this->belongsTo(State::class);
    }

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function area() {
        return $this->belongsTo(Area::class);
    }

    public function pincode() {
        return $this->belongsTo(Pincode::class);
    }

    public function leadSource() {
        return $this->belongsTo(LeadSource::class);
    }

    public function siteLeadMappings()
    {
        return $this->hasMany(SiteLeadMapping::class);
    }

    public function sites()
    {
        return $this->hasManyThrough(
            Site::class,
            SiteLeadMapping::class,
            'lead_id',   // FK on mapping
            'id',        // FK on sites
            'id',        // Local key on leads
            'site_id'    // Local key on mapping
        );
    }
}
