<?php
namespace App\Models;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Application;

/**
 * @method static create(mixed $data)
 * @method static findOrFail($id)
 * @method static where(string $string, mixed $categoryId)
 * @method static Active()
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'is_active', 'image', 'category_id', 'tax_id', 'uom_id'
    ];

    protected $appends = ['image_url'];
    protected $casts = ['is_active' => 'boolean'];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('is_active', 1);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    /**
     * @return BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'uom_id');
    }

    /**
     * @return HasMany
     */
    public function projectStocks(): HasMany
    {
        return $this->hasMany(ProjectStock::class);
    }

    /**
     * @return Application|string|UrlGenerator
     */
    public function getImageUrlAttribute(): Application|string|UrlGenerator
    {
        return generate_file_url($this->image);
    }
}
