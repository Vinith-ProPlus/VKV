<?php

namespace App\Models\Admin\ManageProjects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\Html\Editor\Fields\BelongsTo;

/**
 * @method static findOrFail($id)
 * @method static create(array $all)
 * @method static whereHas(string $string, \Closure $param)
 */
class ProjectTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'stage_id',
        'name',
        'date',
        'image',
        'description',
        'status',
        'created_by_id',
        'completed_at',
    ];

    /**
     * @return BelongsToAlias
     */
    public function stage(): BelongsToAlias
    {
        return $this->BelongsTo(ProjectStage::class);
    }

    /**
     * @return BelongsToAlias
     */
    public function project(): BelongsToAlias
    {
        return $this->BelongsTo(Project::class);
    }

    /**
     * @return BelongsToAlias
     */
    public function created_by(): BelongsToAlias
    {
        return $this->BelongsTo(User::class);
    }
}

