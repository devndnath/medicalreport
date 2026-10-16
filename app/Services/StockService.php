<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Single place where stock ever moves. Every call here posts exactly one
 * stock_transactions row and keeps medicine_batches.remaining_quantity in
 * sync, so reports never recompute balances ad hoc.
 *
 * Valuation policy: FIFO by batch (expiry date, then received date, then id).
 * Historical issue cost is preserved on the transaction/issue-item at the
 * moment it is posted and never recalculated when later purchase prices
 * change (test case #16 in the acceptance criteria).
 */
class StockService
{
    public function openingBalance(Medicine $medicine, float $quantity, ?string $batchNumber = null, string $date = null, bool $costUnavailable = true, ?float $unitCost = null): MedicineBatch
    {
        return DB::transaction(function () use ($medicine, $quantity, $batchNumber, $date, $costUnavailable, $unitCost) {
            $date = $date ?? now()->toDateString();

            $batch = MedicineBatch::create([
                'factory_id' => $medicine->factory_id,
                'medicine_id' => $medicine->id,
                'batch_number' => $batchNumber ?? 'OPENING-'.$medicine->sku,
                'expiry_date' => null,
                'received_date' => $date,
                'received_quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'unit_cost' => $unitCost ?? 0,
                'cost_unavailable' => $costUnavailable,
                'status' => 'active',
                'is_opening_balance' => true,
            ]);

            $this->postTransaction($medicine, 'opening', quantityIn: $quantity, batch: $batch, unitCost: $costUnavailable ? null : $unitCost, costUnavailable: $costUnavailable, reference: $batch, date: $date);

            return $batch;
        });
    }

    public function receivePurchase(\App\Models\PurchaseItem $item): MedicineBatch
    {
        return DB::transaction(function () use ($item) {
            $batch = MedicineBatch::create([
                'factory_id' => $item->medicine->factory_id,
                'medicine_id' => $item->medicine_id,
                'purchase_item_id' => $item->id,
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'received_date' => $item->purchase->purchase_date,
                'received_quantity' => $item->base_unit_quantity,
                'remaining_quantity' => $item->base_unit_quantity,
                'unit_cost' => $item->base_unit_cost,
                'cost_unavailable' => false,
                'status' => 'active',
            ]);

            $this->postTransaction(
                $item->medicine,
                'purchase_receipt',
                quantityIn: $item->base_unit_quantity,
                batch: $batch,
                unitCost: $item->base_unit_cost,
                reference: $item->purchase,
                date: $item->purchase->purchase_date,
            );

            return $batch;
        });
    }

    /**
     * Issue `quantity` (in base unit) of a medicine using FIFO batches.
     * Throws if stock is insufficient or an active batch has expired.
     *
     * @return array{total_cost: float, cost_unavailable: bool, consumptions: array<array{batch: MedicineBatch, quantity: float, unit_cost: ?float}>}
     */
    public function issueFifo(Medicine $medicine, float $quantity, $reference = null): array
    {
        return DB::transaction(function () use ($medicine, $quantity, $reference) {
            $remainingToIssue = $quantity;
            $totalCost = 0.0;
            $costUnavailable = false;
            $consumptions = [];

            $batches = $medicine->activeBatchesFifo()->lockForUpdate()->get();

            foreach ($batches as $batch) {
                if ($remainingToIssue <= 0) {
                    break;
                }

                if ($batch->isExpired()) {
                    continue; // expired batches may never be issued
                }

                $take = min($batch->remaining_quantity, $remainingToIssue);
                if ($take <= 0) {
                    continue;
                }

                $batch->remaining_quantity -= $take;
                if ($batch->remaining_quantity <= 0) {
                    $batch->status = 'depleted';
                }
                $batch->save();

                if ($batch->cost_unavailable) {
                    $costUnavailable = true;
                } else {
                    $totalCost += $take * $batch->unit_cost;
                }

                $consumptions[] = ['batch' => $batch, 'quantity' => $take, 'unit_cost' => $batch->cost_unavailable ? null : $batch->unit_cost];

                $this->postTransaction($medicine, 'issue', quantityOut: $take, batch: $batch, unitCost: $batch->cost_unavailable ? null : $batch->unit_cost, costUnavailable: $batch->cost_unavailable, reference: $reference);

                $remainingToIssue -= $take;
            }

            if ($remainingToIssue > 0.0000001) {
                throw new RuntimeException("Insufficient stock for {$medicine->displayName()}: short by {$remainingToIssue} {$medicine->base_unit}.");
            }

            return [
                'total_cost' => round($totalCost, 2),
                'cost_unavailable' => $costUnavailable,
                'consumptions' => $consumptions,
            ];
        });
    }

    public function returnToBatch(MedicineBatch $batch, float $quantity, string $condition, $reference = null): void
    {
        DB::transaction(function () use ($batch, $quantity, $condition, $reference) {
            if ($condition === 'usable') {
                $batch->remaining_quantity += $quantity;
                if ($batch->status === 'depleted') {
                    $batch->status = 'active';
                }
                $batch->save();

                $this->postTransaction($batch->medicine, 'return', quantityIn: $quantity, batch: $batch, unitCost: $batch->cost_unavailable ? null : $batch->unit_cost, costUnavailable: $batch->cost_unavailable, reference: $reference);
            } else {
                // Damaged returns are written off, not returned to usable stock.
                $this->postTransaction($batch->medicine, 'damaged', quantityOut: 0, batch: $batch, reference: $reference, remarks: 'Returned in damaged condition; written off, not restocked.');
            }
        });
    }

    /**
     * Move stock outside the normal receipt/issue flow. Stock only exists
     * in batches, so every adjustment changes real batches:
     *
     * - With a batch given, that batch is adjusted.
     * - Without one (e.g. reconciling a physical count), an "out" is taken
     *   from the active batches in FIFO order, and an "in" is added to the
     *   most recently received usable batch (or to a new correction batch
     *   if the medicine has none).
     *
     * One ledger row is posted per batch touched, so the ledger and the
     * batches can never disagree.
     */
    public function adjust(Medicine $medicine, ?MedicineBatch $batch, string $direction, float $quantity, string $reason, $reference = null, ?string $remarks = null): void
    {
        DB::transaction(function () use ($medicine, $batch, $direction, $quantity, $reason, $reference, $remarks) {
            $type = match (true) {
                $reason === 'damaged' => 'damaged',
                $reason === 'expired' => 'expired',
                $direction === 'in' => 'adjustment_in',
                default => 'adjustment_out',
            };

            if ($batch) {
                $this->adjustBatch($medicine, $type, $batch, $direction, $quantity, $reason, $reference, $remarks);

                return;
            }

            if ($direction === 'in') {
                $target = $this->latestUsableBatch($medicine) ?? $this->createCorrectionBatch($medicine, $quantity);
                $this->adjustBatch($medicine, $type, $target, 'in', $quantity, $reason, $reference, $remarks);

                return;
            }

            $remaining = $quantity;
            foreach ($medicine->activeBatchesFifo()->lockForUpdate()->get() as $candidate) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min((float) $candidate->remaining_quantity, $remaining);
                if ($take <= 0) {
                    continue;
                }

                $this->adjustBatch($medicine, $type, $candidate, 'out', $take, $reason, $reference, $remarks);
                $remaining -= $take;
            }

            if ($remaining > 0.0000001) {
                throw new RuntimeException("Adjustment-out quantity exceeds available stock for {$medicine->displayName()} by {$remaining} {$medicine->base_unit}.");
            }
        });
    }

    protected function adjustBatch(Medicine $medicine, string $type, MedicineBatch $batch, string $direction, float $quantity, string $reason, $reference, ?string $remarks): void
    {
        if ($direction === 'in') {
            $batch->remaining_quantity += $quantity;
            if ($batch->status === 'depleted') {
                $batch->status = 'active';
            }
        } else {
            if ($batch->remaining_quantity < $quantity) {
                throw new RuntimeException('Adjustment-out quantity exceeds batch remaining quantity.');
            }
            $batch->remaining_quantity -= $quantity;

            // Only a batch that has been written off completely changes status;
            // removing a few damaged/expired units must not take the rest of
            // the batch out of usable stock.
            if ($batch->remaining_quantity <= 0) {
                $batch->status = in_array($reason, ['damaged', 'expired'], true) ? $reason : 'depleted';
            }
        }
        $batch->save();

        $this->postTransaction(
            $medicine,
            $type,
            quantityIn: $direction === 'in' ? $quantity : 0,
            quantityOut: $direction === 'out' ? $quantity : 0,
            batch: $batch,
            unitCost: ! $batch->cost_unavailable ? $batch->unit_cost : null,
            costUnavailable: (bool) $batch->cost_unavailable,
            reference: $reference,
            remarks: $remarks,
        );
    }

    protected function latestUsableBatch(Medicine $medicine): ?MedicineBatch
    {
        return $medicine->batches()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString()))
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->first();
    }

    /** A medicine with no usable batch gets a cost-unknown batch to hold stock found in a count. */
    protected function createCorrectionBatch(Medicine $medicine, float $quantity): MedicineBatch
    {
        return MedicineBatch::create([
            'factory_id' => $medicine->factory_id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'ADJ-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'received_date' => now()->toDateString(),
            'received_quantity' => $quantity,
            'remaining_quantity' => 0, // adjustBatch() adds the quantity
            'unit_cost' => 0,
            'cost_unavailable' => true,
            'status' => 'depleted',
            'remarks' => 'Created by a stock adjustment; cost unknown.',
        ]);
    }

    protected function postTransaction(
        Medicine $medicine,
        string $type,
        float $quantityIn = 0,
        float $quantityOut = 0,
        ?MedicineBatch $batch = null,
        ?float $unitCost = null,
        bool $costUnavailable = false,
        $reference = null,
        ?string $date = null,
        ?string $remarks = null,
    ): StockTransaction {
        // Every caller updates the batch before posting, so the batches
        // already reflect this movement; adding it again would double-count.
        $closing = $medicine->currentStock();

        return StockTransaction::create([
            'factory_id' => $medicine->factory_id,
            'transaction_number' => 'TXN-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'transaction_date' => $date ?? now()->toDateString(),
            'transaction_type' => $type,
            'medicine_id' => $medicine->id,
            'medicine_batch_id' => $batch?->id,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'running_balance' => $closing,
            'unit_cost' => $unitCost,
            'transaction_value' => $unitCost !== null ? round(($quantityIn - $quantityOut) * $unitCost, 2) : null,
            'cost_unavailable' => $costUnavailable,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'user_id' => Auth::id(),
            'remarks' => $remarks,
        ]);
    }
}
