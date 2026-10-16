<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use BelongsToFactory, HasFactory, SoftDeletes;

    protected $fillable = [
        'factory_id', 'sku', 'name', 'generic_name', 'strength', 'dosage_form',
        'medicine_category_id', 'base_unit', 'purchase_unit', 'issue_unit',
        'units_per_purchase_pack', 'manufacturer', 'preferred_supplier_id',
        'default_purchase_price', 'minimum_stock_level', 'reorder_level',
        'storage_location', 'prescription_required', 'status', 'remarks',
    ];

    protected $casts = [
        'units_per_purchase_pack' => 'decimal:4',
        'default_purchase_price' => 'decimal:2',
        'minimum_stock_level' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'prescription_required' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function activeBatchesFifo()
    {
        return $this->batches()
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->orderBy('received_date')
            ->orderBy('id');
    }

    public function currentStock(): float
    {
        return (float) $this->batches()->where('status', 'active')->sum('remaining_quantity');
    }

    public function currentStockValue(): float
    {
        return (float) $this->batches()
            ->where('status', 'active')
            ->where('cost_unavailable', false)
            ->get()
            ->sum(fn ($b) => $b->remaining_quantity * $b->unit_cost);
    }

    public function isLowStock(): bool
    {
        return $this->currentStock() <= (float) $this->reorder_level;
    }

    public function displayName(): string
    {
        return trim($this->name.($this->strength ? " ({$this->strength})" : ''));
    }
}
