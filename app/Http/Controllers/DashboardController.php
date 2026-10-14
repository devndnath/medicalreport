<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineIssueItem;
use App\Models\MedicineIssueVoucher;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $label] = $this->resolvePeriod($request);

        $kpi = [
            'purchase_cost' => (float) PurchaseItem::whereHas('purchase', fn ($q) => $q->where('status', 'confirmed')->whereBetween('purchase_date', [$from, $to]))->sum('line_total'),
            'issue_cost' => (float) MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->where('cost_unavailable', false)->sum('total_cost'),
            'inventory_value' => (float) MedicineBatch::where('status', 'active')->where('cost_unavailable', false)->get()->sum(fn ($b) => $b->remaining_quantity * $b->unit_cost),
            'total_medicines' => Medicine::where('status', 'active')->count(),
            'units_received' => (float) PurchaseItem::whereHas('purchase', fn ($q) => $q->where('status', 'confirmed')->whereBetween('purchase_date', [$from, $to]))->sum('base_unit_quantity'),
            'units_issued' => (float) MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->sum('base_unit_quantity'),
            'low_stock_items' => Medicine::where('status', 'active')->get()->filter(fn ($m) => $m->isLowStock())->count(),
            'expiring_soon' => MedicineBatch::where('status', 'active')->whereNotNull('expiry_date')->where('expiry_date', '>', Carbon::today())->where('expiry_date', '<=', Carbon::today()->addDays(90))->count(),
            'expired_items' => MedicineBatch::where('status', 'active')->whereNotNull('expiry_date')->where('expiry_date', '<=', Carbon::today())->count(),
            'employees_served' => MedicineIssueVoucher::where('status', 'posted')->whereBetween('issued_at', [$from, $to])->distinct('employee_id')->count('employee_id'),
            'monthly_expense' => (float) MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]))->where('cost_unavailable', false)->sum('total_cost'),
            'pending_purchase_bills' => (float) Purchase::where('status', 'confirmed')->where('outstanding_amount', '>', 0)->sum('outstanding_amount'),
        ];

        $monthlyPurchase = PurchaseItem::selectRaw("DATE_FORMAT(purchases.purchase_date, '%Y-%m') as ym, SUM(purchase_items.line_total) as total")
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.status', 'confirmed')
            ->where('purchases.purchase_date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

        $monthlyIssue = MedicineIssueItem::selectRaw("DATE_FORMAT(medicine_issue_vouchers.issued_at, '%Y-%m') as ym, SUM(medicine_issue_items.total_cost) as total")
            ->join('medicine_issue_vouchers', 'medicine_issue_vouchers.id', '=', 'medicine_issue_items.medicine_issue_voucher_id')
            ->where('medicine_issue_vouchers.status', 'posted')
            ->where('medicine_issue_items.cost_unavailable', false)
            ->where('medicine_issue_vouchers.issued_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

        $topMedicines = MedicineIssueItem::selectRaw('medicine_id, SUM(base_unit_quantity) as qty')
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))
            ->groupBy('medicine_id')->orderByDesc('qty')->limit(10)
            ->with('medicine')->get();

        $topEmployees = MedicineIssueVoucher::selectRaw('employee_id, SUM(total_cost) as cost')
            ->where('status', 'posted')->whereBetween('issued_at', [$from, $to])
            ->groupBy('employee_id')->orderByDesc('cost')->limit(10)
            ->with('employee')->get();

        $departmentExpense = MedicineIssueVoucher::selectRaw('department_id, SUM(total_cost) as cost')
            ->where('status', 'posted')->whereBetween('issued_at', [$from, $to])
            ->groupBy('department_id')->orderByDesc('cost')
            ->with('department')->get();

        $categoryValue = MedicineBatch::where('status', 'active')->where('cost_unavailable', false)
            ->get()->groupBy(fn ($b) => $b->medicine->category->name ?? 'Uncategorized')
            ->map(fn ($rows) => $rows->sum(fn ($b) => $b->remaining_quantity * $b->unit_cost));

        $recentActivity = DB::table('stock_transactions')
            ->join('medicines', 'medicines.id', '=', 'stock_transactions.medicine_id')
            ->select('stock_transactions.*', 'medicines.name as medicine_name')
            ->orderByDesc('stock_transactions.created_at')
            ->limit(20)->get();

        return view('dashboard.index', compact(
            'kpi', 'from', 'to', 'label', 'monthlyPurchase', 'monthlyIssue',
            'topMedicines', 'topEmployees', 'departmentExpense', 'categoryValue', 'recentActivity'
        ));
    }

    protected function resolvePeriod(Request $request): array
    {
        $range = $request->get('range', 'this_month');
        $today = Carbon::today();

        [$from, $to] = match ($range) {
            'today' => [$today->copy(), $today->copy()->endOfDay()],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'this_quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfQuarter()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom' => [
                Carbon::parse($request->get('from', $today->toDateString())),
                Carbon::parse($request->get('to', $today->toDateString()))->endOfDay(),
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        return [$from, $to, $range];
    }
}
