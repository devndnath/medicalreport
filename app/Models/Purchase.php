<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use BelongsToFactory, HasFactory, SoftDeletes;

    protected $fillable = [
        'factory_id', 'purchase_number', 'purchase_date', 'source_type', 'received_from',
        'supplier_id', 'supplier_invoice_number',
        'discount_amount', 'tax_amount', 'other_charges', 'grand_total',
        'payment_status', 'payment_amount', 'outstanding_amount', 'status',
        'received_by', 'invoice_attachment_path', 'notes', 'confirmed_at',
    ];

    public function isFromSupplier(): bool
    {
        return $this->source_type === 'supplier';
    }

    public function sourceLabel(): string
    {
        return match ($this->source_type) {
            'store' => 'Store'.($this->received_from ? " ({$this->received_from})" : ''),
            'accounts' => 'Accounts'.($this->received_from ? " ({$this->received_from})" : ''),
            default => $this->supplier->name ?? 'Supplier',
        };
    }

    protected $casts = [
        'purchase_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function recalculateTotals(): void
    {
        $linesTotal = $this->items()->sum('line_total');
        $this->grand_total = $linesTotal;
        $this->outstanding_amount = max(0, $this->grand_total - $this->payment_amount);
        $this->payment_status = $this->payment_amount <= 0
            ? 'unpaid'
            : ($this->outstanding_amount <= 0 ? 'paid' : 'partial');
        $this->save();
    }
}
