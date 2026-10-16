<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalStockCount extends Model
{
    use BelongsToFactory, HasFactory;

    protected $fillable = ['factory_id', 'count_number', 'count_date', 'counted_by', 'status'];

    protected $casts = ['count_date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(PhysicalStockCountItem::class);
    }
}
