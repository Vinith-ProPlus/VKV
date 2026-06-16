<?php

namespace App\Models;

use App\Models\Admin\ManageProjects\SiteStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static where(string $string, mixed $siteId)
 */
class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'site_id',
        'site_stage_id',
        'remarks',
        'is_damaged',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteStage(): BelongsTo
    {
        return $this->belongsTo(SiteStage::class, 'site_stage_id');
    }

    /** @deprecated Use siteStage() */
    public function stage(): BelongsTo
    {
        return $this->siteStage();
    }

    /** @deprecated Use site() and site.project */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'module_id')
            ->where('module_name', 'Blog');
    }
}
