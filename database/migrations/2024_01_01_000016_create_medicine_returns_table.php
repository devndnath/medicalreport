<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->foreignId('medicine_issue_item_id')->nullable()->constrained('medicine_issue_items')->nullOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('medicine_batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->decimal('quantity', 12, 4); // base_unit
            $table->enum('condition', ['usable', 'damaged'])->default('usable');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_returns');
    }
};
