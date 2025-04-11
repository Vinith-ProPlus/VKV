<?php

namespace App\Models\Admin\Labor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static whereIsActive(string $string)
 */
class LaborDesignation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active'];
}
