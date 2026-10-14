<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineIssueVoucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class IssueService
{
    public function __construct(protected StockService $stock) {}

    /**
     * Create and post a multi-line issue voucher. Stock availability for
     * every line is validated before anything is deducted, so a voucher
     * either posts completely or not at all.
     *
     * @param  array<int, array{medicine_id:int, quantity_issued:float, issue_unit:string}>  $lines
     */
    public function createVoucher(Employee $employee, array $lines, ?string $symptoms = null, ?string $remarks = null): MedicineIssueVoucher
    {
        return DB::transaction(function () use ($employee, $lines, $symptoms, $remarks) {
            if (empty($lines)) {
                throw new RuntimeException('An issue voucher must have at least one medicine line.');
            }

            // Pre-validate stock for every line before posting any of them.
            foreach ($lines as $line) {
                $medicine = Medicine::findOrFail($line['medicine_id']);
                $baseQty = $this->toBaseUnits($medicine, (float) $line['quantity_issued'], $line['issue_unit'] ?? $medicine->issue_unit);
                if ($medicine->currentStock() < $baseQty) {
                    throw new RuntimeException("Insufficient stock for {$medicine->displayName()}: available ".$medicine->currentStock()." {$medicine->base_unit}, requested {$baseQty}.");
                }
            }

            $voucher = MedicineIssueVoucher::create([
                'voucher_number' => 'MIV-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'issued_at' => now(),
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'symptoms' => $symptoms,
                'issued_by' => Auth::id(),
                'status' => 'posted',
                'remarks' => $remarks,
            ]);

            $voucherTotal = 0.0;
            $anyCostUnavailable = false;

            foreach ($lines as $line) {
                $medicine = Medicine::findOrFail($line['medicine_id']);
                $issueUnit = $line['issue_unit'] ?? $medicine->issue_unit;
                $baseQty = $this->toBaseUnits($medicine, (float) $line['quantity_issued'], $issueUnit);

                $result = $this->stock->issueFifo($medicine, $baseQty, $voucher);

                $item = $voucher->items()->create([
                    'medicine_id' => $medicine->id,
                    'quantity_issued' => $line['quantity_issued'],
                    'issue_unit' => $issueUnit,
                    'base_unit_quantity' => $baseQty,
                    'unit_cost' => $result['cost_unavailable'] ? null : round($result['total_cost'] / max($baseQty, 0.0001), 6),
                    'total_cost' => $result['cost_unavailable'] ? null : $result['total_cost'],
                    'cost_unavailable' => $result['cost_unavailable'],
                ]);

                foreach ($result['consumptions'] as $c) {
                    $item->batchConsumptions()->create([
                        'medicine_batch_id' => $c['batch']->id,
                        'quantity' => $c['quantity'],
                        'unit_cost' => $c['unit_cost'],
                    ]);
                }

                if ($result['cost_unavailable']) {
                    $anyCostUnavailable = true;
                } else {
                    $voucherTotal += $result['total_cost'];
                }
            }

            $voucher->total_cost = round($voucherTotal, 2);
            $voucher->has_cost_unavailable_lines = $anyCostUnavailable;
            $voucher->save();

            return $voucher;
        });
    }

    /**
     * Controlled reversal: restores stock to the exact batches it came
     * from and marks the original voucher reversed. Posted vouchers are
     * never silently edited (acceptance criteria require this).
     */
    public function reverseVoucher(MedicineIssueVoucher $voucher): MedicineIssueVoucher
    {
        return DB::transaction(function () use ($voucher) {
            if ($voucher->status === 'reversed') {
                throw new RuntimeException('This voucher has already been reversed.');
            }

            foreach ($voucher->items as $item) {
                foreach ($item->batchConsumptions as $consumption) {
                    $this->stock->returnToBatch($consumption->batch, $consumption->quantity, 'usable', $voucher);
                }
            }

            $voucher->status = 'reversed';
            $voucher->save();

            return $voucher;
        });
    }

    protected function toBaseUnits(Medicine $medicine, float $quantity, ?string $unit): float
    {
        // In this system issue_unit is normally the same as base_unit; the
        // conversion factor lives on units_per_purchase_pack for purchases.
        // If a future issue_unit differs from base_unit, extend this with a
        // per-medicine issue-unit conversion table rather than guessing.
        return $quantity;
    }
}
