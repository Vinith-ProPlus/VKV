<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 * @method static whereIn(string $string, mixed $documentIds)
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'module_name',
        'module_id',
        'file_path',
        'file_name',
        'uploaded_by',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return generate_file_url($this->file_path);
    }
}

