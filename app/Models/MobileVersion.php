<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static first()
 */
class MobileVersion extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $appends = ['UpdateImageUrl'];

    protected $fillable = [
        "id",
        "logo",
        "title",
        "description",
        "current_version",
        "new_version",
        "android_link",
        "ios_link",
        "submit_text",
        "ignore_text",
        "update_type",
        "update_to",
    ];

    public function getUpdateImageUrlAttribute($value)
    {
        return generate_file_url($this->logo);
    }
}
