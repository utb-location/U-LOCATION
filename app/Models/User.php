<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name',
        'username', 'email', 'password', 'role', 'active', 'must_change_password', 'last_login_at'])]
#[Hidden(['password',
        'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccess(string $permission): bool
    {
        $active = $this->active === null ? true : (bool) $this->active;
        if (! $active) return false;
        if ($this->role === 'admin') return true;

        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $role = $this->accessRole;
            if ($role && $role->active) {
                if ($role->slug === 'super_admin') return true;
                return $role->permissions()->where('slug', $permission)->exists();
            }
        }

        return $active && in_array($this->role, config("roles.permissions.$permission", []), true);
    }

    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function roleLabel(): string
    {
        if (Schema::hasTable('roles')) {
            $role = $this->accessRole;
            if ($role) return $role->name;
        }

        return config("roles.labels.$this->role", $this->role);
    }
}
