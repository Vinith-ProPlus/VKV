<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static whereHas(string $string, \Closure $param)
 * @method static where(string $string, int $int)
 * @method static Active()
 */
class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active'];
    public function products(): HasMany
    {
        return $this->HasMany(Product::class, 'category_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('is_active', 1);
    }
}
