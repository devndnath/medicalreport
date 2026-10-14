<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number', 'transaction_date', 'transaction_type', 'medicine_id',
        'medicine_batch_id', 'quantity_in', 'quantity_out', 'running_balance',
        'unit_cost', 'transaction_value', 'cost_unavailable', 'reference_type',
        'reference_id', 'is_reversed', 'reversed_by_transaction_id', 'user_id', 'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'cost_unavailable' => 'boolean',
        'is_reversed' => 'boolean',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
