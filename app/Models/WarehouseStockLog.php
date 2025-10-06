<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $warehouseId)
 */
class WarehouseStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'category_id',
        'product_id',
        'previous_quantity',
        'quantity',
        'balance_quantity',
        'user_id',
        'time',
        'type',
        'remarks'
    ];

    protected $casts = [
        'time' => 'datetime',
        'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'balance_quantity' => 'decimal:2'
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
