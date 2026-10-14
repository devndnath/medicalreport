<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalStockCountItem extends Model
{
    protected $fillable = [
        'physical_stock_count_id', 'medicine_id', 'system_quantity',
        'counted_quantity', 'discrepancy', 'remarks',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
