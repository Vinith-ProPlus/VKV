<?php

namespace App\Models\Admin\ManageProjects;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectSpecifications extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['spec_name', 'spec_values', 'is_active'];
}
