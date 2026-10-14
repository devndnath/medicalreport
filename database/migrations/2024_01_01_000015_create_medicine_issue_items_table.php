<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_issue_voucher_id')->constrained('medicine_issue_vouchers')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->decimal('quantity_issued', 12, 4); // in issue_unit
            $table->string('issue_unit');
            $table->decimal('base_unit_quantity', 12, 4); // converted to base_unit for stock deduction
            $table->decimal('unit_cost', 14, 6)->nullable(); // FIFO-weighted cost per base unit at issue time
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->boolean('cost_unavailable')->default(false);
            $table->timestamps();
        });

        // Breakdown of exactly which batches an issue line consumed, for FIFO auditability.
        Schema::create('medicine_issue_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_issue_item_id')->constrained('medicine_issue_items')->cascadeOnDelete();
            $table->foreignId('medicine_batch_id')->constrained('medicine_batches');
            $table->decimal('quantity', 12, 4); // base_unit taken from this batch
            $table->decimal('unit_cost', 14, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_issue_item_batches');
        Schema::dropIfExists('medicine_issue_items');
    }
};
