<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // generated Medicine ID / SKU
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable(); // e.g. 500mg
            $table->enum('dosage_form', ['tablet', 'capsule', 'syrup', 'drop', 'gel', 'cream', 'liquid', 'injection', 'supply', 'other'])->default('other');
            $table->foreignId('medicine_category_id')->nullable()->constrained('medicine_categories')->nullOnDelete();
            $table->string('base_unit'); // tablet, capsule, ml, piece, roll, tube, bottle... all stock is tracked in this unit
            $table->string('purchase_unit')->nullable(); // box, strip, bottle...
            $table->string('issue_unit')->nullable(); // tablet, piece...
            $table->decimal('units_per_purchase_pack', 12, 4)->default(1); // conversion factor: 1 purchase_unit = X base_unit
            $table->string('manufacturer')->nullable();
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('default_purchase_price', 12, 2)->nullable(); // per base unit, for new-purchase pre-fill only — never used to cost historical issues
            $table->decimal('minimum_stock_level', 12, 4)->default(0);
            $table->decimal('reorder_level', 12, 4)->default(0);
            $table->string('storage_location')->nullable();
            $table->boolean('prescription_required')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
            $table->unique(['name', 'strength', 'dosage_form'], 'medicines_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
