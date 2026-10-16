<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Factory;
use App\Models\ImportBatch;
use App\Models\MedicineBatch;
use App\Models\MedicineIssueItem;
use App\Models\MedicineIssueVoucher;
use App\Models\MedicineReturn;
use App\Models\PhysicalStockCount;
use App\Models\Purchase;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use App\Services\IssueService;
use App\Services\PurchaseService;
use App\Services\StockService;
use Database\Seeders\Concerns\SeedsPerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Replays ~45 days of believable activity for each factory by calling the
 * real services (never inserting ledger rows by hand), with the clock set
 * to each simulated day. That keeps batches, the stock ledger, FIFO costs
 * and running balances consistent, and gives the daily / weekly / monthly
 * / custom-date reports something to show.
 *
 * Everything here is SAMPLE data. It is deterministic (seeded RNG), so a
 * given factory gets the same history every time it is seeded.
 */
class SampleActivitySeeder extends Seeder
{
    use SeedsPerFactory;

    /** Days ago => [supplier name | null, source_type, received_from, invoice prefix, payment mode, final state, lines] */
    protected function purchasePlan(): array
    {
        return [
            // [name, strength, qty, free, discount %, tax %]
            40 => ['Square Pharmaceuticals Ltd.', 'supplier', null, 'SQ', 'partial', 'confirm', [
                ['Napa', '500mg', 500, 0, 2, 0],
                ['Entacyd Plus', '400mg', 300, 0, 2, 0],
                ['Alzine', '50mg', 200, 0, 0, 0],
                ['Omidon', '10mg', 200, 0, 0, 0],
                ['Rolac', '10mg', 150, 0, 0, 0],
            ]],
            25 => ['Healthcare Supplies Bangladesh', 'supplier', null, 'HC', 'paid', 'confirm', [
                ['Cotton', null, 20, 0, 0, 5],
                ['Surgical Gauze', null, 25, 0, 0, 5],
                ['Roll Bandage', '2-4 inch', 30, 0, 0, 5],
                ['Green Strip', null, 40, 0, 0, 5],
                ['Nix', null, 50, 0, 0, 5],
            ]],
            12 => [null, 'store', 'Store — Nasir Uddin (Store Keeper)', null, 'paid', 'confirm', [
                ['ORS', null, 150, 20, 0, 0],
                ['Syrup Entacyd Plus', null, 500, 0, 0, 0],
                ['Savlon', 'Liquid', 10, 0, 0, 0],
            ]],
            5 => [null, 'accounts', 'Accounts — Md. Rafiqul Islam', null, 'paid', 'confirm', [
                ['Rolac', '10mg', 100, 0, 0, 0],
                ['Omidon', '10mg', 100, 0, 0, 0],
                ['Volinac', null, 10, 0, 0, 0],
                ['Burna', 'Cream', 10, 0, 0, 0],
            ]],
            3 => ['Delta Pharma Ltd.', 'supplier', null, 'DP', 'unpaid', 'cancel', [
                ['Trego', 'Cream', 12, 0, 0, 0],
                ['Clofenac', 'Gel', 12, 0, 0, 0],
            ]],
            0 => ['Incepta Pharmaceuticals Ltd.', 'supplier', null, 'IN', 'unpaid', 'draft', [
                ['ORS', null, 200, 0, 0, 0],
                ['Napa', '500mg', 400, 0, 2, 0],
            ]],
        ];
    }

    /** Why people visit the medical room, and what they are given. [name, strength, min qty, max qty] */
    protected function scenarios(): array
    {
        return [
            ['symptoms' => 'Fever and headache', 'weight' => 20, 'lines' => [['Napa', '500mg', 4, 10]]],
            ['symptoms' => 'Gastric pain / acidity', 'weight' => 16, 'lines' => [['Entacyd Plus', '400mg', 6, 10], ['Omidon', '10mg', 4, 6]]],
            ['symptoms' => 'Diarrhea / loose motion', 'weight' => 10, 'lines' => [['ORS', null, 2, 4], ['Amodis DS', '400mg', 4, 6]]],
            ['symptoms' => 'Muscle / joint pain', 'weight' => 10, 'lines' => [['Rolac', '10mg', 4, 6], ['Clofenac', 'Gel', 1, 1]]],
            ['symptoms' => 'Cold and cough', 'weight' => 9, 'lines' => [['Fenadin', '120mg', 6, 10]]],
            ['symptoms' => 'Cut / minor injury', 'weight' => 9, 'lines' => [['Green Strip', null, 2, 4], ['Roll Bandage', '2-4 inch', 1, 1]]],
            ['symptoms' => 'Allergy / skin itching', 'weight' => 8, 'lines' => [['Alzine', '50mg', 4, 6], ['Trego', 'Cream', 1, 1]]],
            ['symptoms' => 'Minor burn', 'weight' => 4, 'lines' => [['Burna', 'Cream', 1, 1]]],
            ['symptoms' => 'Wound cleaning', 'weight' => 3, 'lines' => [['Savlon', 'Liquid', 1, 1], ['Cotton', null, 1, 1]]],
            ['symptoms' => 'Eye irritation', 'weight' => 3, 'lines' => [['Optimox', null, 1, 1]]],
        ];
    }

    protected StockService $stock;

    protected PurchaseService $purchases;

    protected IssueService $issues;

    protected Carbon $realToday;

    public function run(): void
    {
        $this->realToday = Carbon::today();

        $this->eachFactory(function (Factory $factory) {
            if (Purchase::exists() || MedicineIssueVoucher::exists()) {
                return; // activity already seeded for this factory
            }
            if (! MedicineBatch::exists()) {
                $this->command?->warn("No opening stock for {$factory->name}; run OpeningStockSeeder first. Skipping activity.");

                return;
            }

            mt_srand(1000 + $factory->id);
            $this->seedFactory($factory);
        });

        mt_srand();
    }

    protected function seedFactory(Factory $factory): void
    {
        $this->stock = app(StockService::class);
        $this->purchases = app(PurchaseService::class);
        $this->issues = app(IssueService::class);

        $store = $this->actingAs($factory, 'store_officer');
        $admin = $this->userFor($factory, 'admin');
        $employees = Employee::orderBy('id')->get();
        $plan = $this->purchasePlan();

        for ($d = 44; $d >= 0; $d--) {
            $day = $this->realToday->copy()->subDays($d);
            Carbon::setTestNow($day->copy()->setTime(9, 30));

            if ($d === 44) {
                $this->seedImportBatch($factory, $admin);
            }

            if (isset($plan[$d])) {
                $this->makePurchase($factory, $day, $plan[$d]);
            }

            // Medical room is open Saturday–Thursday; Friday is the weekly holiday.
            if ($d <= 30 && $d >= 1 && $day->dayOfWeek !== Carbon::FRIDAY && $employees->isNotEmpty()) {
                $visits = ($r = mt_rand(1, 10)) <= 4 ? 1 : ($r <= 8 ? 2 : 3);
                for ($i = 0; $i < $visits; $i++) {
                    Carbon::setTestNow($day->copy()->setTime(mt_rand(9, 16), mt_rand(0, 59)));
                    $this->issueVoucher($this->pickEmployee($employees), $this->pickScenario());
                }
            }

            $this->runEvents($d, $store, $admin);
        }

        $this->seedLoginHistory($factory);
    }

    protected function runEvents(int $d, ?User $store, ?User $admin): void
    {
        switch ($d) {
            case 33:
                $this->adjustStock($admin, 'Napa', '500mg', 'OPEN', 'in', 'data_correction', 20, 'Uncounted strips found behind the shelf during opening-stock check.');
                break;
            case 22:
                $this->adjustStock($admin, 'Roll Bandage', '2-4 inch', 'OPEN', 'out', 'other', 2, 'Used for first-aid training drill.');
                break;
            case 20:
                $voucher = MedicineIssueVoucher::where('status', 'posted')->latest('id')->first();
                if ($voucher) {
                    $this->issues->reverseVoucher($voucher);
                    $voucher->update(['remarks' => 'Reversed: issued to the wrong employee, stock restored to the same batches.']);
                }
                break;
            case 14:
                $this->recordReturn('Napa', '500mg', 6, 2, 'usable', 'Fever settled, unused tablets handed back.', $store);
                break;
            case 9:
                $this->recordReturn('Green Strip', null, 2, 1, 'damaged', 'Strip packet torn, cannot be reissued.', $store);
                break;
            case 7:
                $this->adjustStock($admin, 'Hexisol', null, 'HEX-OLD', 'out', 'damaged', null, 'Bottles cracked in storage; whole lot written off.');
                break;
            case 4:
                $this->adjustStock($admin, 'Syrup Entacyd Plus', null, 'SYR-EXP', 'out', 'expired', null, 'Lot passed its expiry date; removed from usable stock.');
                break;
            case 2:
                $this->recordPhysicalCount($store);
                break;
        }
    }

    // ------------------------------------------------------------------
    // Purchases
    // ------------------------------------------------------------------

    protected function makePurchase(Factory $factory, Carbon $day, array $spec): void
    {
        [$supplierName, $sourceType, $receivedFrom, $invoicePrefix, $paymentMode, $finalState, $lines] = $spec;

        $supplier = $supplierName ? Supplier::where('name', $supplierName)->first() : null;
        if ($sourceType === 'supplier' && ! $supplier) {
            return;
        }

        $items = [];
        $totalDiscount = $totalTax = $grand = 0.0;

        foreach ($lines as [$name, $strength, $qty, $free, $discountPct, $taxPct]) {
            $medicine = $this->medicine($name, $strength);
            $cost = OpeningStockSeeder::catalog()[OpeningStockSeeder::key($name, $strength)]['cost'];
            $price = round($cost * 1.08, 2); // prices drift ~8% up since opening stock, so FIFO costing is visible

            $gross = $qty * $price;
            $discount = round($gross * $discountPct / 100, 2);
            $tax = round(($gross - $discount) * $taxPct / 100, 2);

            $totalDiscount += $discount;
            $totalTax += $tax;
            $grand += round($gross - $discount + $tax, 2);

            $items[] = [
                'medicine_id' => $medicine->id,
                'batch_number' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3)).'-'.$day->format('ymd'),
                'expiry_date' => $this->realToday->copy()->addMonths(24)->toDateString(),
                'purchase_quantity' => $qty,
                'free_quantity' => $free,
                'unit_purchase_price' => $price,
                'line_discount' => $discount,
                'line_tax' => $tax,
                'line_other_charges' => 0,
            ];
        }

        $paid = match ($paymentMode) {
            'paid' => round($grand, 2),
            'partial' => round($grand * 0.5, 2),
            default => 0,
        };

        $purchase = $this->purchases->createDraft([
            'factory_id' => $factory->id,
            'purchase_number' => 'PO-'.$day->format('Ymd').'-'.Str::upper(Str::random(5)),
            'purchase_date' => $day->toDateString(),
            'source_type' => $sourceType,
            'received_from' => $receivedFrom,
            'supplier_id' => $supplier?->id,
            'supplier_invoice_number' => $invoicePrefix ? $invoicePrefix.'-'.$day->format('ymd').'-'.(100 + $factory->id) : null,
            'discount_amount' => round($totalDiscount, 2),
            'tax_amount' => round($totalTax, 2),
            'other_charges' => 0,
            'payment_amount' => $paid,
            'notes' => match ($finalState) {
                'cancel' => 'Sample: order cancelled before delivery.',
                'draft' => 'Sample: pending order, not yet received.',
                default => 'Sample purchase.',
            },
        ], $items);

        match ($finalState) {
            'confirm' => $this->purchases->confirm($purchase),
            'cancel' => $this->purchases->cancel($purchase),
            default => null, // left as a draft on purpose
        };
    }

    // ------------------------------------------------------------------
    // Issuing medicine to employees
    // ------------------------------------------------------------------

    protected function pickScenario(): array
    {
        $scenarios = $this->scenarios();
        $roll = mt_rand(1, array_sum(array_column($scenarios, 'weight')));

        foreach ($scenarios as $scenario) {
            $roll -= $scenario['weight'];
            if ($roll <= 0) {
                return $scenario;
            }
        }

        return $scenarios[0];
    }

    protected function pickEmployee($employees): Employee
    {
        // Most visits come from a core group of employees; the rest are spread out.
        $pool = (mt_rand(1, 10) <= 7 ? $employees->take(6) : $employees)->values();

        return $pool[mt_rand(0, $pool->count() - 1)];
    }

    protected function issueVoucher(Employee $employee, array $scenario): void
    {
        $lines = [];
        foreach ($scenario['lines'] as [$name, $strength, $min, $max]) {
            $medicine = $this->medicine($name, $strength);
            $lines[] = [
                'medicine_id' => $medicine->id,
                'quantity_issued' => mt_rand($min, $max),
                'issue_unit' => $medicine->issue_unit,
            ];
        }
        $acknowledged = mt_rand(1, 10) <= 8;

        try {
            $voucher = $this->issues->createVoucher($employee, $lines, $scenario['symptoms']);
        } catch (RuntimeException $e) {
            return; // not enough stock on that day; skip rather than fail the whole seed
        }

        if ($acknowledged) {
            $voucher->update(['receiver_acknowledged' => true]);
        }
    }

    // ------------------------------------------------------------------
    // Returns, adjustments, physical count
    // ------------------------------------------------------------------

    protected function recordReturn(string $name, ?string $strength, float $minIssued, float $qty, string $condition, string $reason, ?User $receiver): void
    {
        $medicine = $this->medicine($name, $strength);

        $item = MedicineIssueItem::where('medicine_id', $medicine->id)
            ->where('quantity_issued', '>=', $minIssued)
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted'))
            ->latest('id')
            ->first();
        if (! $item) {
            return;
        }

        $consumption = $item->batchConsumptions()->first();
        $batch = $consumption ? MedicineBatch::find($consumption->medicine_batch_id) : null;
        if (! $batch) {
            return;
        }

        $return = MedicineReturn::create([
            'return_number' => 'RET-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'return_date' => now()->toDateString(),
            'medicine_issue_item_id' => $item->id,
            'medicine_id' => $item->medicine_id,
            'medicine_batch_id' => $batch->id,
            'quantity' => $qty,
            'condition' => $condition,
            'received_by' => $receiver?->id,
            'reason' => $reason,
        ]);

        $this->stock->returnToBatch($batch, $qty, $condition, $return);
    }

    /**
     * @param  string  $lot  'OPEN' = the standard opening-balance lot, otherwise an exact batch number
     * @param  float|null  $quantity  null = the batch's whole remaining quantity
     */
    protected function adjustStock(?User $approver, string $name, ?string $strength, string $lot, string $direction, string $reason, ?float $quantity, string $remarks): void
    {
        $medicine = $this->medicine($name, $strength);
        $batch = $lot === 'OPEN'
            ? $medicine->batches()->where('is_opening_balance', true)->orderBy('id')->first()
            : $medicine->batches()->where('batch_number', $lot)->first();
        if (! $batch) {
            return;
        }

        $quantity ??= (float) $batch->remaining_quantity;
        if ($quantity <= 0) {
            return;
        }

        $previous = Auth::user();
        if ($approver) {
            Auth::setUser($approver);
        }

        try {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => 'ADJ-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'adjustment_date' => now()->toDateString(),
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch->id,
                'direction' => $direction,
                'reason' => $reason,
                'quantity' => $quantity,
                'approved_by' => $approver?->id,
                'remarks' => $remarks,
            ]);

            $this->stock->adjust($medicine, $batch, $direction, $quantity, $reason, $adjustment, $remarks);
        } finally {
            if ($previous) {
                Auth::setUser($previous);
            }
        }
    }

    /** Left "in progress" on purpose so the review / reconcile screen has something to show. */
    protected function recordPhysicalCount(?User $counter): void
    {
        $count = PhysicalStockCount::create([
            'count_number' => 'PSC-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'count_date' => now()->toDateString(),
            'counted_by' => $counter?->id,
            'status' => 'in_progress',
        ]);

        // [name, strength, counted minus system]
        foreach ([['Napa', '500mg', -3], ['ORS', null, 0], ['Entacyd Plus', '400mg', 0], ['Savlon', 'Liquid', -1], ['Cotton', null, 0], ['Alzine', '50mg', -2]] as [$name, $strength, $delta]) {
            $medicine = $this->medicine($name, $strength);
            $system = $medicine->currentStock();
            $counted = max(0, $system + $delta);

            $count->items()->create([
                'medicine_id' => $medicine->id,
                'system_quantity' => $system,
                'counted_quantity' => $counted,
                'discrepancy' => $counted - $system,
                'remarks' => $delta === 0 ? null : 'Sample discrepancy found on the shelf.',
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Import history and audit trail
    // ------------------------------------------------------------------

    /** A finished import so the Import screen's history is not empty. The file itself is not real. */
    protected function seedImportBatch(Factory $factory, ?User $admin): void
    {
        $batch = ImportBatch::create([
            'factory_id' => $factory->id,
            'original_filename' => 'legacy-medicine-workbook (sample).xlsx',
            'stored_path' => 'imports/sample-legacy-workbook.xlsx',
            'uploaded_by' => $admin?->id,
            'status' => 'confirmed',
            'total_rows' => 8,
            'valid_rows' => 5,
            'error_rows' => 1,
            'duplicate_rows' => 2,
            'confirmed_at' => now(),
        ]);

        $rows = [
            ['Medicine List', 2, ['Medicine' => 'Napa 500mg'], 'committed', null],
            ['Medicine List', 3, ['Medicine' => 'ORS'], 'committed', null],
            ['Medicine List', 4, ['Medicine' => 'Entacyd Plus 400mg'], 'committed', null],
            ['Medicine List', 5, ['Medicine' => 'Alzine 50mg'], 'committed', null],
            ['Medicine List', 6, ['Medicine' => 'Omidon 10mg'], 'committed', null],
            ['Medicine List', 7, ['Medicine' => 'Napa 500mg'], 'duplicate', ['Already exists in the medicine master.']],
            ['Medicine List', 8, ['Medicine' => 'ORS'], 'duplicate', ['Already exists in the medicine master.']],
            ['Daily Report', 12, ['Employee' => 'Unknown', 'Emp. ID' => ''], 'error', ['Missing employee ID.']],
        ];

        foreach ($rows as [$sheet, $rowNumber, $raw, $status, $errors]) {
            $batch->rows()->create([
                'source_sheet' => $sheet,
                'source_row' => $rowNumber,
                'raw_data' => $raw,
                'row_status' => $status,
                'errors' => $errors,
            ]);
        }
    }

    /** Model changes are audited automatically by AuditObserver; this adds sign-in entries. */
    protected function seedLoginHistory(Factory $factory): void
    {
        $users = User::where('factory_id', $factory->id)->orderBy('id')->get();

        foreach ($users as $i => $user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'logged_in',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'ip_address' => '192.168.1.'.(20 + $i),
                'url' => 'http://localhost/login',
            ]);
        }
    }
}
