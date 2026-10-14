<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_issue_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->dateTime('issued_at');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete(); // snapshot at issue time
            $table->text('symptoms')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('receiver_acknowledged')->default(false);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->boolean('has_cost_unavailable_lines')->default(false);
            $table->enum('status', ['posted', 'reversed'])->default('posted');
            $table->foreignId('reversed_by_voucher_id')->nullable()->constrained('medicine_issue_vouchers')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_issue_vouchers');
    }
};
