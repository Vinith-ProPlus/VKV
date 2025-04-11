<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static firstOrCreate(array $array)
 * @method static where(string $string, int $int)
 */
class SupportType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
