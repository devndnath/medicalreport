<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineIssueVoucher;
use App\Models\Purchase;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        foreach ([Medicine::class, Purchase::class, MedicineIssueVoucher::class, StockAdjustment::class, Employee::class, Supplier::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
