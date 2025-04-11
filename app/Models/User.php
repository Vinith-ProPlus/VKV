<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\Site;
use App\Models\Admin\Master\City;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static create(mixed $data)
 * @method static findOrFail($id)
 * @method static where(string $string, mixed $id)
 * @method static whereEmail(mixed $email)
 * @method static firstOrCreate(array $supervisorData)
 * @method static updateOrCreate(array $array, array $contractorData)
 * @method static find(int $userId)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'dob',
        'mobile',
        'alternate_mobile',
        'address',
        'state_id',
        'city_id',
        'pincode_id',
        'district_id',
        'role_id',
        'password',
        'active_status',
        'image',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function pincode(): BelongsTo
    {
        return $this->belongsTo(Pincode::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_supervisor', 'supervisor_id', 'site_id');
    }

    public function tasks()
    {
        return ProjectTask::whereHas('project.site.supervisors', function ($query) {
            $query->where('users.id', $this->id);
        });
    }
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(UserDeviceLocation::class);
    }
}
