<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_number')->unique();
            $table->date('count_date');
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['in_progress', 'reconciled'])->default('in_progress');
            $table->timestamps();
        });

        Schema::create('physical_stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_stock_count_id')->constrained('physical_stock_counts')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->decimal('system_quantity', 12, 4); // base_unit, as of count date
            $table->decimal('counted_quantity', 12, 4); // base_unit, physically counted
            $table->decimal('discrepancy', 12, 4); // counted - system
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_stock_count_items');
        Schema::dropIfExists('physical_stock_counts');
    }
};
