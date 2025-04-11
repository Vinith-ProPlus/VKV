<?php

namespace App\Models;

use App\Models\Admin\ManageProjects\ProjectStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 */
class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'project_stage_id',
        'remarks',
        'is_damaged'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToAlias
     */
    public function stage(): BelongsToAlias
    {
        return $this->BelongsTo(ProjectStage::class, 'project_stage_id');
    }

    /**
     * @return BelongsToAlias
     */
    public function project(): BelongsToAlias
    {
        return $this->BelongsTo(Project::class);
    }
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'module_id')
            ->where('module_name', 'Blog');
    }
}
