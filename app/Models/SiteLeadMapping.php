<?php

namespace App\Models;

use App\Models\Lead;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteLeadMapping extends Model
{
    use HasFactory;

    protected $table = 'site_lead_mappings';
    
    protected $fillable = [
        'site_id',
        'lead_id',
        'status',
        'remarks'
    ];

    // Optional: make sure status is always one of these
    // const STATUS_OPEN = 'Open';
    // const STATUS_BOOKED = 'Booked';
    // const STATUS_SOLD = 'Sold';

    // Relationships

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
