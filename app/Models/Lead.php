<?php

namespace App\Models;

use App\Models\Admin\Master\City;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['lead_title', 'first_name', 'last_name', 'address', 'state_id', 'district_id', 'city_id', 'pincode_id', 'gst_number', 'email', 'mobile_number', 'whatsapp_number', 'lead_source_id', 'lead_status_id', 'lead_owner_id', 'lead_follow_by_id', 'image'];

    public function state() {
        return $this->belongsTo(State::class);
    }

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function city() {
        return $this->belongsTo(City::class);
    }

    public function pincode() {
        return $this->belongsTo(Pincode::class);
    }

    public function leadSource() {
        return $this->belongsTo(LeadSource::class);
    }

    public function leadStatus() {
        return $this->belongsTo(LeadStatus::class);
    }

    public function leadOwner() {
        return $this->belongsTo(User::class, 'lead_owner_id');
    }

    public function leadFollowBy() {
        return $this->belongsTo(User::class, 'lead_follow_by_id');
    }
}
