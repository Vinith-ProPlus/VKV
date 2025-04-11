<?php

namespace App\Models\Admin\Master;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $all)
 * @method static findOrFail($id)
 */
class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'district_id', 'is_active'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
