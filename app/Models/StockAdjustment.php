<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use BelongsToFactory, HasFactory;

    protected $fillable = [
        'factory_id', 'adjustment_number', 'adjustment_date', 'medicine_id', 'medicine_batch_id',
        'direction', 'reason', 'quantity', 'approved_by', 'remarks',
    ];

    protected $casts = ['adjustment_date' => 'date'];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
