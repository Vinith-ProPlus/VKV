<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 */
class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['ticket_number', 'user_id', 'subject', 'status', 'support_type_id'];

    public static function generateTicketNumber(): string
    {
        $datePrefix = now()->format('Y-md'); // e.g., 2025-0330

        // Get the last ticket number for today
        $lastTicket = self::where('ticket_number', 'LIKE', "{$datePrefix}%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        // Extract the last 4 digits and increment
        $nextNumber = $lastTicket ? ((int)substr($lastTicket->ticket_number, -4) + 1) : 1;

        // Format the next number with leading zeros
        return $datePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($ticket) {
            $ticket->ticket_number = self::generateTicketNumber();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function support_type(): BelongsTo
    {
        return $this->belongsTo(SupportType::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }
}
