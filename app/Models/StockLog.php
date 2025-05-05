<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $projectId)
 */
class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
