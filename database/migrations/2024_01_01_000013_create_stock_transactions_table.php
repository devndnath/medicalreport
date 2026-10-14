<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The single source-of-truth ledger. Every stock movement of any kind
        // posts exactly one row here. Closing = Opening + Receipts + Returns
        // + AdjustIn - Issues - AdjustOut, enforced in StockService, never
        // computed ad-hoc in a report.
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', [
                'opening', 'purchase_receipt', 'issue', 'return', 'adjustment_in',
                'adjustment_out', 'damaged', 'expired', 'transfer_in', 'transfer_out', 'reversal',
            ]);
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('medicine_batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->decimal('quantity_in', 12, 4)->default(0); // base_unit
            $table->decimal('quantity_out', 12, 4)->default(0); // base_unit
            $table->decimal('running_balance', 12, 4)->nullable(); // closing balance for this medicine after this transaction
            $table->decimal('unit_cost', 14, 6)->nullable();
            $table->decimal('transaction_value', 14, 2)->nullable();
            $table->boolean('cost_unavailable')->default(false);
            $table->string('reference_type')->nullable(); // Purchase, MedicineIssueVoucher, MedicineReturn, StockAdjustment, PhysicalStockCount
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by_transaction_id')->nullable()->constrained('stock_transactions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['medicine_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
