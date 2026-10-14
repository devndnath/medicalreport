<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'medicine_id', 'batch_number', 'expiry_date',
        'purchase_quantity', 'purchase_unit', 'free_quantity', 'unit_purchase_price',
        'line_discount', 'line_tax', 'line_other_charges', 'line_total',
        'base_unit_quantity', 'base_unit_cost',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): HasOne
    {
        return $this->hasOne(MedicineBatch::class);
    }
}
