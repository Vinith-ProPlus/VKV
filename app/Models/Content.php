<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 */
class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'content', 'is_active'];
}

