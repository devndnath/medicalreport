<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('supplier_invoice_number')->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('payment_amount', 14, 2)->default(0);
            $table->decimal('outstanding_amount', 14, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
