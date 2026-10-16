<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each factory is a fully separate operating unit: its own medicines,
     * employees, departments, purchases/receives, and stock ledger. A
     * Super Admin can switch between factories (one at a time, never
     * merged); every other role is permanently tied to one factory.
     */
    public function up(): void
    {
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // short code, e.g. "F1", "F2"
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factories');
    }
};
