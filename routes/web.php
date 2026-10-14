<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineReturnController;
use App\Http\Controllers\PhysicalStockCountController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    // All authenticated roles (including read-only Management) can view the dashboard.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Admin + Store Officer: master data & day-to-day operations ----
    Route::middleware('role:admin,store_officer')->group(function () {
        Route::resource('medicines', MedicineController::class);
        Route::post('medicines/{medicine}/opening-stock', [MedicineController::class, 'openingStock'])->name('medicines.opening-stock');
        Route::post('medicine-categories', [MedicineCategoryController::class, 'store'])->name('medicine-categories.store');
        Route::put('medicine-categories/{category}', [MedicineCategoryController::class, 'update'])->name('medicine-categories.update');
        Route::delete('medicine-categories/{category}', [MedicineCategoryController::class, 'destroy'])->name('medicine-categories.destroy');
        Route::get('medicine-categories', [MedicineCategoryController::class, 'index'])->name('medicine-categories.index');

        Route::resource('employees', EmployeeController::class);
        Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show']);

        Route::resource('issues', IssueController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('issues/{voucher}/print', [IssueController::class, 'print'])->name('issues.print');
        Route::post('issues/{voucher}/reverse', [IssueController::class, 'reverse'])->name('issues.reverse');
        Route::post('issues/{voucher}/acknowledge', [IssueController::class, 'acknowledge'])->name('issues.acknowledge');

        Route::resource('returns', MedicineReturnController::class)->only(['index', 'create', 'store']);
        Route::resource('adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store']);

        Route::get('stock/ledger', [StockLedgerController::class, 'index'])->name('stock.ledger');
        Route::get('stock/low-stock', [StockLedgerController::class, 'lowStock'])->name('stock.low-stock');
        Route::get('stock/expiry', [StockLedgerController::class, 'expiry'])->name('stock.expiry');
        Route::get('medicines/{medicine}/batches', [StockLedgerController::class, 'batches'])->name('stock.batches');

        Route::get('stock/counts', [PhysicalStockCountController::class, 'index'])->name('stock.counts.index');
        Route::get('stock/counts/create', [PhysicalStockCountController::class, 'create'])->name('stock.counts.create');
        Route::post('stock/counts', [PhysicalStockCountController::class, 'store'])->name('stock.counts.store');
        Route::get('stock/counts/{count}', [PhysicalStockCountController::class, 'show'])->name('stock.counts.show');
        Route::post('stock/counts/{count}/reconcile', [PhysicalStockCountController::class, 'reconcile'])->name('stock.counts.reconcile');

        Route::get('import', [ImportController::class, 'create'])->name('import.create');
        Route::post('import', [ImportController::class, 'store'])->name('import.store');
        Route::get('import/{batch}', [ImportController::class, 'show'])->name('import.show');
        Route::post('import/{batch}/confirm', [ImportController::class, 'confirm'])->name('import.confirm');
        Route::post('import/{batch}/cancel', [ImportController::class, 'cancel'])->name('import.cancel');
    });

    // ---- Admin + Accounts: purchasing, suppliers, financials ----
    Route::middleware('role:admin,accounts,store_officer')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
        Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::get('purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
        Route::get('purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
    });

    // ---- Reports: Admin, Accounts and Management (read-only) can all view ----
    Route::middleware('role:admin,accounts,management,store_officer')->prefix('reports')->name('reports.')->group(function () {
        Route::get('purchase-expense', [ReportController::class, 'purchaseExpense'])->name('purchase-expense');
        Route::get('employee-expense', [ReportController::class, 'employeeExpense'])->name('employee-expense');
        Route::get('department-expense', [ReportController::class, 'departmentExpense'])->name('department-expense');
        Route::get('medicine-consumption', [ReportController::class, 'medicineConsumption'])->name('medicine-consumption');
        Route::get('daily-usage', [ReportController::class, 'dailyUsage'])->name('daily-usage');
        Route::get('monthly-usage', [ReportController::class, 'monthlyUsage'])->name('monthly-usage');
        Route::get('highest-cost-medicines', [ReportController::class, 'highestCostMedicines'])->name('highest-cost-medicines');
        Route::get('supplier-performance', [ReportController::class, 'supplierPerformance'])->name('supplier-performance');
        Route::get('inventory-valuation', [ReportController::class, 'inventoryValuation'])->name('inventory-valuation');

        Route::get('medicine-consumption/export/excel', [ReportExportController::class, 'medicineConsumptionExcel'])->name('medicine-consumption.export-excel');
        Route::get('employee-expense/export/pdf', [ReportExportController::class, 'employeeExpensePdf'])->name('employee-expense.export-pdf');
    });

    // ---- Admin only: users, audit logs, settings ----
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show', 'edit']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
