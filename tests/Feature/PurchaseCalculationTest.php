<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers acceptance criteria #2-3: purchase with discount/tax computes
 * the correct line and grand totals, and a draft purchase never touches
 * stock until explicitly confirmed.
 */
class PurchaseCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_total_applies_discount_and_tax_correctly(): void
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create(['units_per_purchase_pack' => 10]);
        $service = app(PurchaseService::class);

        $purchase = $service->createDraft([
            'purchase_number' => 'PO-TEST-1',
            'purchase_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
        ], [[
            'medicine_id' => $medicine->id,
            'batch_number' => 'B1',
            'purchase_quantity' => 10,
            'unit_purchase_price' => 100,
            'line_discount' => 50,
            'line_tax' => 20,
        ]]);

        // (10 * 100) - 50 + 20 = 970
        $this->assertEquals(970, $purchase->items->first()->line_total);
        $this->assertEquals(970, $purchase->grand_total);
        $this->assertEquals('draft', $purchase->status);
        $this->assertEquals(0, $medicine->currentStock(), 'Draft purchases must not affect stock.');
    }

    public function test_confirming_a_purchase_receives_stock_but_draft_does_not(): void
    {
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->create(['units_per_purchase_pack' => 1]);
        $service = app(PurchaseService::class);

        $purchase = $service->createDraft([
            'purchase_number' => 'PO-TEST-2',
            'purchase_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
        ], [[
            'medicine_id' => $medicine->id,
            'batch_number' => 'B2',
            'purchase_quantity' => 25,
            'unit_purchase_price' => 4,
        ]]);

        $this->assertEquals(0, $medicine->currentStock());

        $service->confirm($purchase);

        $this->assertEquals(25, $medicine->currentStock());
        $this->assertEquals('confirmed', $purchase->fresh()->status);
    }
}
