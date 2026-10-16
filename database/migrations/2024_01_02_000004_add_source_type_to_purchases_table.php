<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Not every factory buys from outside suppliers — the medical officer
     * often just receives stock handed over internally by the factory
     * Store or by Accounts. source_type records which of the three this
     * receipt was; supplier_id is only required when source_type is
     * 'supplier'. received_from captures a free-text name/note for the
     * Store/Accounts case (who released it), since there is no supplier
     * record to point to.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('source_type', ['supplier', 'store', 'accounts'])->default('supplier')->after('purchase_date');
            $table->string('received_from')->nullable()->after('source_type'); // who released stock, for store/accounts receipts
        });

        // Avoid requiring doctrine/dbal just to relax a NOT NULL constraint:
        // drop the existing required FK and re-add the column as nullable.
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('received_from')->constrained('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'received_from', 'supplier_id']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->constrained('suppliers');
        });
    }
};
