<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id', 'purchase_item_id', 'batch_number', 'expiry_date',
        'received_date', 'received_quantity', 'remaining_quantity', 'unit_cost',
        'cost_unavailable', 'status', 'is_opening_balance', 'remarks',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_date' => 'date',
        'cost_unavailable' => 'boolean',
        'is_opening_balance' => 'boolean',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function expiresWithin(int $days): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return ! $this->isExpired() && $this->expiry_date->lte(Carbon::today()->addDays($days));
    }
}
