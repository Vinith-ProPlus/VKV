<?php

namespace App\Models;

use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 */
class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'mobile', 'rating', 'feedback', 'project_id', 'user_id'];

    public function project(): BelongsToAlias
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }
}

