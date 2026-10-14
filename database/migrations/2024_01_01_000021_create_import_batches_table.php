<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks every Excel import attempt: preview, validation errors, and
        // the final confirm-and-commit, so historical imports stay auditable
        // and the original uploaded workbook is never overwritten.
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['previewed', 'confirmed', 'cancelled'])->default('previewed');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->json('raw_data');
            $table->enum('row_status', ['valid', 'error', 'duplicate', 'committed'])->default('valid');
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
        Schema::dropIfExists('import_batches');
    }
};
