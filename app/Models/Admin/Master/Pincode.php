<?php

namespace App\Models\Admin\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $all)
 * @method static findOrFail($id)
 */
class Pincode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['pincode', 'area_id', 'is_active'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
