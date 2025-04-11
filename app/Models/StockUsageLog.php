<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'category_id',
        'product_id',
        'previous_quantity',
        'quantity',
        'balance_quantity',
        'taken_by',
        'taken_at',
        'remarks'
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'balance_quantity' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function takenByUser()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }
}
