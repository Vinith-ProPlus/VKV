<?php

namespace App\Models;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $site_id)
 */
class SiteStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'category_id',
        'product_id',
        'quantity',
        'last_updated_by',
        'last_transaction_type'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
