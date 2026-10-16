<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single operating factory/unit. Every factory has its own medicines,
 * employees, departments, purchases/receives and stock — nothing is
 * shared between factories except the Super Admin's ability to switch
 * which one they are currently viewing.
 */
class Factory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
