<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'force_password_change',
        'last_login_at',
        'created_by',
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
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active && $this->roles()->exists();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function endAllSessions(): void
    {
        $this->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->saveQuietly();

        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $this->id)->delete();
        }
    }

    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if ($user->isDirty('is_active') && ! $user->is_active && $user->isSuperAdmin() && \App\Policies\UserPolicy::isLastSuperAdmin($user)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('The last active super admin cannot be disabled.');
            }
        });

        static::updated(function (User $user) {
            if ($user->wasChanged('is_active') && ! $user->is_active) {
                $user->endAllSessions();
            }
        });

        static::deleting(function (User $user) {
            if ($user->isSuperAdmin() && \App\Policies\UserPolicy::isLastSuperAdmin($user)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('The last active super admin cannot be deleted.');
            }
        });
    }
}
