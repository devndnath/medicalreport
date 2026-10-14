<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_quantity', 12, 4); // in purchase_unit
            $table->string('purchase_unit');
            $table->decimal('free_quantity', 12, 4)->default(0);
            $table->decimal('unit_purchase_price', 12, 4); // price per purchase_unit
            $table->decimal('line_discount', 12, 2)->default(0);
            $table->decimal('line_tax', 12, 2)->default(0);
            $table->decimal('line_other_charges', 12, 2)->default(0);
            $table->decimal('line_total', 14, 2); // computed: (qty*price) + free-item treatment - discount + tax + charges
            $table->decimal('base_unit_quantity', 12, 4); // purchase_quantity * units_per_purchase_pack, in medicine base_unit
            $table->decimal('base_unit_cost', 14, 6); // line_total / base_unit_quantity — used for FIFO costing
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
