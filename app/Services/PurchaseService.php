<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseService
{
    public function __construct(protected StockService $stock) {}

    /**
     * Create a draft purchase with its line items. Line totals and the
     * purchase grand total are computed here — never typed in by hand —
     * so Line Total = (qty*price) + free-item cost treatment - discount
     * + tax + other charges, and stock is NOT touched until confirm().
     */
    public function createDraft(array $purchaseData, array $items): Purchase
    {
        return DB::transaction(function () use ($purchaseData, $items) {
            $this->guardDuplicateInvoice($purchaseData['supplier_id'], $purchaseData['supplier_invoice_number'] ?? null);

            $purchase = Purchase::create([
                ...$purchaseData,
                'status' => 'draft',
                'received_by' => Auth::id(),
            ]);

            foreach ($items as $row) {
                $this->addItem($purchase, $row);
            }

            $purchase->recalculateTotals();

            return $purchase;
        });
    }

    public function addItem(Purchase $purchase, array $row): PurchaseItem
    {
        $medicine = \App\Models\Medicine::findOrFail($row['medicine_id']);
        $qty = (float) $row['purchase_quantity'];
        $price = (float) $row['unit_purchase_price'];
        $free = (float) ($row['free_quantity'] ?? 0);
        $discount = (float) ($row['line_discount'] ?? 0);
        $tax = (float) ($row['line_tax'] ?? 0);
        $charges = (float) ($row['line_other_charges'] ?? 0);

        // Paid-quantity cost only; free items add zero cost but do add stock.
        $lineTotal = ($qty * $price) - $discount + $tax + $charges;
        $baseQty = ($qty + $free) * (float) $medicine->units_per_purchase_pack;
        $baseCost = $baseQty > 0 ? $lineTotal / $baseQty : 0;

        return $purchase->items()->create([
            'medicine_id' => $medicine->id,
            'batch_number' => $row['batch_number'],
            'expiry_date' => $row['expiry_date'] ?? null,
            'purchase_quantity' => $qty,
            'purchase_unit' => $row['purchase_unit'] ?? $medicine->purchase_unit,
            'free_quantity' => $free,
            'unit_purchase_price' => $price,
            'line_discount' => $discount,
            'line_tax' => $tax,
            'line_other_charges' => $charges,
            'line_total' => round($lineTotal, 2),
            'base_unit_quantity' => $baseQty,
            'base_unit_cost' => $baseCost,
        ]);
    }

    /**
     * Confirm a draft purchase: creates batches, posts receipts to the
     * stock ledger, and updates supplier/payment info. Cancelled or
     * draft purchases never reach here, so they never touch stock.
     */
    public function confirm(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            if ($purchase->status !== 'draft') {
                throw new RuntimeException('Only draft purchases can be confirmed.');
            }

            foreach ($purchase->items as $item) {
                $this->stock->receivePurchase($item);
            }

            $purchase->status = 'confirmed';
            $purchase->confirmed_at = now();
            $purchase->save();

            return $purchase;
        });
    }

    public function cancel(Purchase $purchase): void
    {
        if ($purchase->status === 'confirmed') {
            throw new RuntimeException('A confirmed purchase cannot be cancelled directly; reverse it through a stock adjustment instead.');
        }

        $purchase->status = 'cancelled';
        $purchase->save();
    }

    protected function guardDuplicateInvoice(int $supplierId, ?string $invoiceNumber): void
    {
        if (! $invoiceNumber) {
            return;
        }

        $exists = Purchase::where('supplier_id', $supplierId)
            ->where('supplier_invoice_number', $invoiceNumber)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            throw new RuntimeException("Supplier invoice #{$invoiceNumber} has already been recorded for this supplier. Confirm this is not a duplicate before proceeding.");
        }
    }
}
