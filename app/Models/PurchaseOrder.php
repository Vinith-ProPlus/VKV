<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @method static where(string $string, string $string1, string $string2)
 * @method static select(string $string, string $string1, string $string2, string $string3, string $string4, string $string5)
 */
class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_request_id', 'project_id', 'supervisor_id', 'order_id', 'order_date', 'remarks', 'status', 'approved_by'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateOrderID(): string
    {
        $datePrefix = now()->format('Y-md'); // Use consistent prefix

        // Lock the table range to avoid race condition
        $lastOrder = self::where('order_id', 'LIKE', "O-{$datePrefix}%")->orderBy('order_id', 'desc')->first();

        $nextNumber = $lastOrder ? ((int)substr($lastOrder->order_id, -4) + 1) : 1;

        return "O-" . $datePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($order) {
            $order->order_id = self::generateOrderID();
            $order->order_date = Carbon::now();
            $order->status = 'Pending';
            $order->approved_by = Auth::id();
        });
    }
}
