<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 */
class PurchaseRequestDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['purchase_request_id', 'category_id', 'product_id', 'quantity'];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
