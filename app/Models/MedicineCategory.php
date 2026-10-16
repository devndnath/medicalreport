<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineCategory extends Model
{
    use BelongsToFactory, HasFactory;

    protected $fillable = ['factory_id', 'name', 'description'];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
