<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each factory now runs its own separate catalog, so names/codes that
     * used to be unique system-wide only need to be unique within a
     * factory (e.g. two factories can both have a "Napa 500mg" or a
     * "Sewing" department without colliding).
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['factory_id', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['employee_code']);
            $table->unique(['factory_id', 'employee_code']);
        });

        Schema::table('medicine_categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['factory_id', 'name']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique('medicines_identity_unique');
            $table->unique(['factory_id', 'sku']);
            $table->unique(['factory_id', 'name', 'strength', 'dosage_form'], 'medicines_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['factory_id', 'name']);
            $table->unique(['name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['factory_id', 'employee_code']);
            $table->unique(['employee_code']);
        });

        Schema::table('medicine_categories', function (Blueprint $table) {
            $table->dropUnique(['factory_id', 'name']);
            $table->unique(['name']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropUnique(['factory_id', 'sku']);
            $table->dropUnique('medicines_identity_unique');
            $table->unique(['sku']);
            $table->unique(['name', 'strength', 'dosage_form'], 'medicines_identity_unique');
        });
    }
};
