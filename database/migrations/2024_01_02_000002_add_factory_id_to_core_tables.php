<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * factory_id is left DB-nullable (so upgrading an existing database
     * never fails outright) but the application always requires and sets
     * it: Super Admin picks a factory when creating master data, every
     * other role is locked to their own factory via the current-factory
     * context. See App\Support\CurrentFactory and App\Models\Concerns\BelongsToFactory.
     */
    protected array $tables = [
        'users', 'departments', 'employees', 'medicine_categories', 'medicines',
        'suppliers', 'purchases', 'medicine_batches', 'medicine_issue_vouchers', 'medicine_returns',
        'stock_adjustments', 'physical_stock_counts', 'import_batches', 'stock_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                // foreignId()->constrained() already indexes the column via its FK.
                $blueprint->foreignId('factory_id')->nullable()->after('id')->constrained('factories')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign($table.'_factory_id_foreign');
                $blueprint->dropColumn('factory_id');
            });
        }
    }
};
