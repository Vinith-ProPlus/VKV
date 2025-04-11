<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['labor_id', 'date', 'amount', 'paid_at'];

    public function labor(): BelongsTo
    {
        return $this->belongsTo(Labor::class);
    }
}
