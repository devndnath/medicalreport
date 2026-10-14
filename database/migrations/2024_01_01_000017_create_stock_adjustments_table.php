<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->date('adjustment_date');
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('medicine_batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->enum('reason', ['damaged', 'expired', 'physical_count_correction', 'data_correction', 'other']);
            $table->decimal('quantity', 12, 4); // base_unit
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
