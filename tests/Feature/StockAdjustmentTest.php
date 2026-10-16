<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stock adjustments must change real batches (so the Medicines page and the
 * ledger agree), and the ledger's running balance must equal the true stock
 * after each movement.
 */
class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected function batch(Medicine $medicine, string $number, float $qty, string $received, ?string $expiry = null, float $cost = 2.0): MedicineBatch
    {
        return $medicine->batches()->create([
            'batch_number' => $number, 'received_date' => $received, 'expiry_date' => $expiry,
            'received_quantity' => $qty, 'remaining_quantity' => $qty,
            'unit_cost' => $cost, 'cost_unavailable' => false, 'status' => 'active',
        ]);
    }

    public function test_partial_damaged_write_off_leaves_the_rest_of_the_batch_usable(): void
    {
        $medicine = Medicine::factory()->create();
        $batch = $this->batch($medicine, 'A', 300, now()->subDays(5)->toDateString());

        (new StockService)->adjust($medicine, $batch, 'out', 1, 'damaged');

        $batch->refresh();
        $this->assertEquals(299, $batch->remaining_quantity);
        $this->assertSame('active', $batch->status);
        $this->assertEquals(299, $medicine->currentStock());
    }

    public function test_writing_off_the_whole_batch_marks_it_with_the_reason(): void
    {
        $medicine = Medicine::factory()->create();
        $batch = $this->batch($medicine, 'A', 3, now()->subDays(5)->toDateString());

        (new StockService)->adjust($medicine, $batch, 'out', 3, 'expired');

        $this->assertSame('expired', $batch->refresh()->status);
        $this->assertEquals(0, $medicine->currentStock());
    }

    public function test_adjustment_out_without_a_batch_takes_stock_in_fifo_order(): void
    {
        $medicine = Medicine::factory()->create();
        $old = $this->batch($medicine, 'OLD', 10, now()->subDays(20)->toDateString(), now()->addMonths(6)->toDateString());
        $new = $this->batch($medicine, 'NEW', 10, now()->subDays(2)->toDateString(), now()->addMonths(12)->toDateString());

        (new StockService)->adjust($medicine, null, 'out', 14, 'physical_count_correction');

        $this->assertEquals(0, $old->refresh()->remaining_quantity);
        $this->assertEquals(6, $new->refresh()->remaining_quantity);
        $this->assertEquals(6, $medicine->currentStock());
    }

    public function test_adjustment_out_without_a_batch_cannot_exceed_available_stock(): void
    {
        $medicine = Medicine::factory()->create();
        $batch = $this->batch($medicine, 'A', 5, now()->subDay()->toDateString());

        try {
            (new StockService)->adjust($medicine, null, 'out', 9, 'physical_count_correction');
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertEquals(5, $batch->refresh()->remaining_quantity); // rolled back
        }
    }

    public function test_adjustment_in_without_a_batch_adds_to_the_latest_usable_batch(): void
    {
        $medicine = Medicine::factory()->create();
        $old = $this->batch($medicine, 'OLD', 10, now()->subDays(20)->toDateString());
        $new = $this->batch($medicine, 'NEW', 10, now()->subDays(2)->toDateString());

        (new StockService)->adjust($medicine, null, 'in', 4, 'physical_count_correction');

        $this->assertEquals(10, $old->refresh()->remaining_quantity);
        $this->assertEquals(14, $new->refresh()->remaining_quantity);
        $this->assertEquals(24, $medicine->currentStock());
    }

    public function test_adjustment_in_for_a_medicine_with_no_batches_creates_a_cost_unknown_batch(): void
    {
        $medicine = Medicine::factory()->create();

        (new StockService)->adjust($medicine, null, 'in', 7, 'data_correction');

        $this->assertEquals(7, $medicine->currentStock());
        $this->assertTrue((bool) $medicine->batches()->first()->cost_unavailable);
    }

    public function test_ledger_running_balance_equals_true_stock_after_every_movement(): void
    {
        $medicine = Medicine::factory()->create();
        $stock = new StockService;

        $stock->openingBalance($medicine, 100);          // 100
        $stock->issueFifo($medicine, 30);                // 70
        $stock->adjust($medicine, null, 'out', 5, 'other'); // 65
        $stock->adjust($medicine, null, 'in', 10, 'data_correction'); // 75

        $balances = \App\Models\StockTransaction::where('medicine_id', $medicine->id)
            ->orderBy('id')->pluck('running_balance')->map(fn ($b) => (float) $b)->all();

        $this->assertSame([100.0, 70.0, 65.0, 75.0], $balances);
        $this->assertEquals(75, $medicine->currentStock());
    }
}
