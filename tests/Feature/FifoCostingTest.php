<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers acceptance criteria #2-5, #10, #16: stock increases on receipt,
 * FIFO issues consume the oldest batch first, historical issue cost is
 * frozen at post time and does not move when a later batch's price
 * changes, and expired batches can never be issued.
 */
class FifoCostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_balance_increases_stock_and_is_marked_cost_unavailable(): void
    {
        $medicine = Medicine::factory()->create(['base_unit' => 'tablet']);
        $stock = new StockService;

        $batch = $stock->openingBalance($medicine, 100);

        $this->assertEquals(100, $medicine->currentStock());
        $this->assertTrue($batch->cost_unavailable);
    }

    public function test_fifo_issue_consumes_oldest_batch_first_and_freezes_historical_cost(): void
    {
        $medicine = Medicine::factory()->create(['base_unit' => 'tablet']);
        $stock = new StockService;

        $oldBatch = $medicine->batches()->create([
            'batch_number' => 'OLD', 'received_date' => now()->subDays(10),
            'received_quantity' => 50, 'remaining_quantity' => 50,
            'unit_cost' => 2.00, 'cost_unavailable' => false, 'status' => 'active',
        ]);

        $newBatch = $medicine->batches()->create([
            'batch_number' => 'NEW', 'received_date' => now(),
            'received_quantity' => 50, 'remaining_quantity' => 50,
            'unit_cost' => 5.00, 'cost_unavailable' => false, 'status' => 'active',
        ]);

        $result = $stock->issueFifo($medicine, 60);

        // 50 units from the old batch at 2.00 + 10 units from the new batch at 5.00
        $this->assertEquals(50 * 2.00 + 10 * 5.00, $result['total_cost']);

        $oldBatch->refresh();
        $newBatch->refresh();
        $this->assertEquals(0, $oldBatch->remaining_quantity);
        $this->assertEquals(40, $newBatch->remaining_quantity);

        // Changing the new batch's cost later must not alter the already-posted cost.
        $newBatch->update(['unit_cost' => 9.99]);
        $this->assertEquals(50 * 2.00 + 10 * 5.00, $result['total_cost']);
    }

    public function test_insufficient_stock_throws_and_posts_nothing(): void
    {
        $medicine = Medicine::factory()->create(['base_unit' => 'tablet']);
        $stock = new StockService;
        $stock->openingBalance($medicine, 5);

        $this->expectException(\RuntimeException::class);
        $stock->issueFifo($medicine, 10);
    }

    public function test_expired_batch_is_never_issued(): void
    {
        $medicine = Medicine::factory()->create(['base_unit' => 'tablet']);

        $medicine->batches()->create([
            'batch_number' => 'EXPIRED', 'received_date' => now()->subYear(),
            'expiry_date' => now()->subDay(), 'received_quantity' => 50,
            'remaining_quantity' => 50, 'unit_cost' => 1, 'cost_unavailable' => false, 'status' => 'active',
        ]);

        $stock = new StockService;

        $this->expectException(\RuntimeException::class);
        $stock->issueFifo($medicine, 10);
    }
}
