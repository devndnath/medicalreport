<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Deliberately does NOT use App\Models\Concerns\BelongsToFactory. The
 * auth system looks up the logged-in user (to find out their factory in
 * the first place) before CurrentFactory is known for the request, so a
 * factory-filtering global scope here would be circular and could lock
 * everyone out. Factory scoping for user management is instead done
 * explicitly in UserManagementController.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'factory_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Named operatingFactory(), not factory() — HasFactory's static
    // factory() method (for test/database factories) owns that name.
    public function operatingFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    public function hasRole(string ...$keys): bool
    {
        return $this->role && in_array($this->role->key, $keys, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
