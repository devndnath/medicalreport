<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique(); // original "Emp. ID" from workbook; nullable allowed only via placeholder flag below
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('designation')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'resigned', 'terminated'])->default('active');
            $table->string('phone')->nullable();
            $table->date('joining_date')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('needs_id_confirmation')->default(false); // true for legacy rows imported without a real Emp ID
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
