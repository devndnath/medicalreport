<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->decimal('received_quantity', 12, 4); // in base_unit
            $table->decimal('remaining_quantity', 12, 4); // in base_unit — decremented by FIFO issues
            $table->decimal('unit_cost', 14, 6); // base_unit cost for this batch (FIFO lot cost)
            $table->boolean('cost_unavailable')->default(false); // true for legacy/opening lots with no known price
            $table->enum('status', ['active', 'expired', 'damaged', 'depleted'])->default('active');
            $table->boolean('is_opening_balance')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['medicine_id', 'expiry_date']);
            $table->index(['medicine_id', 'received_date']);
            $table->unique(['medicine_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
    }
};
