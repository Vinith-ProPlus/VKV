<?php

namespace App\Models;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $array)
 */
class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['supervisor_id', 'site_id', 'product_count', 'remarks', 'status'];

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseRequestDetail::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
