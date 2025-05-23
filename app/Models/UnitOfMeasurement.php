<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitOfMeasurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
