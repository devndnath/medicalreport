<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_number', 'purchase_date', 'supplier_id', 'supplier_invoice_number',
        'discount_amount', 'tax_amount', 'other_charges', 'grand_total',
        'payment_status', 'payment_amount', 'outstanding_amount', 'status',
        'received_by', 'invoice_attachment_path', 'notes', 'confirmed_at',
    ];

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
