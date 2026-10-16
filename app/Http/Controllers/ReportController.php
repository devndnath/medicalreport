<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineIssueItem;
use App\Models\MedicineIssueVoucher;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * @return array{0: Carbon, 1: Carbon, 2: string} [from, to, preset]
     */
    protected function period(Request $request): array
    {
        $preset = $request->get('period', $request->filled('from') || $request->filled('to') ? 'custom' : 'this_month');
        $today = Carbon::today();

        [$from, $to] = match ($preset) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            default => [
                $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : $today->copy()->startOfMonth(),
                $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : $today->copy()->endOfDay(),
            ],
        };

        return [$from, $to, $preset];
    }

    /**
     * Unified Usage Report: who/what consumed medicine, grouped by
     * Employee, Department, or Medicine, over Today / This Week / This
     * Month / a custom date range — with a day-by-day breakdown inside
     * whatever period is chosen.
     */
    public function usageReport(Request $request)
    {
        [$from, $to, $preset] = $this->period($request);
        $groupBy = $request->get('group_by', 'employee');

        $items = MedicineIssueItem::with(['medicine', 'voucher.employee.department'])
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))
            ->get();

        $rows = match ($groupBy) {
            'department' => $items->groupBy(fn ($i) => $i->voucher->employee->department_id ?? 0)->map(fn ($g) => [
                'label' => $g->first()->voucher->employee->department->name ?? 'Unassigned',
                'quantity' => $g->sum('base_unit_quantity'),
                'cost' => $g->where('cost_unavailable', false)->sum('total_cost'),
                'has_cost_unavailable' => $g->where('cost_unavailable', true)->isNotEmpty(),
                'voucher_count' => $g->pluck('medicine_issue_voucher_id')->unique()->count(),
            ])->sortByDesc('cost'),
            'medicine' => $items->groupBy('medicine_id')->map(fn ($g) => [
                'label' => $g->first()->medicine->displayName(),
                'quantity' => $g->sum('base_unit_quantity'),
                'cost' => $g->where('cost_unavailable', false)->sum('total_cost'),
                'has_cost_unavailable' => $g->where('cost_unavailable', true)->isNotEmpty(),
                'voucher_count' => $g->pluck('medicine_issue_voucher_id')->unique()->count(),
            ])->sortByDesc('cost'),
            default => $items->groupBy(fn ($i) => $i->voucher->employee_id)->map(fn ($g) => [
                'label' => $g->first()->voucher->employee->name ?? 'Unknown',
                'sub_label' => $g->first()->voucher->employee->department->name ?? null,
                'quantity' => $g->sum('base_unit_quantity'),
                'cost' => $g->where('cost_unavailable', false)->sum('total_cost'),
                'has_cost_unavailable' => $g->where('cost_unavailable', true)->isNotEmpty(),
                'voucher_count' => $g->pluck('medicine_issue_voucher_id')->unique()->count(),
            ])->sortByDesc('cost'),
        };

        $daily = $items->groupBy(fn ($i) => $i->voucher->issued_at->toDateString())
            ->map(fn ($g, $date) => [
                'date' => $date,
                'quantity' => $g->sum('base_unit_quantity'),
                'cost' => $g->where('cost_unavailable', false)->sum('total_cost'),
            ])->sortKeys();

        $totals = [
            'quantity' => $items->sum('base_unit_quantity'),
            'cost' => $items->where('cost_unavailable', false)->sum('total_cost'),
            'voucher_count' => $items->pluck('medicine_issue_voucher_id')->unique()->count(),
            'has_cost_unavailable' => $items->where('cost_unavailable', true)->isNotEmpty(),
        ];

        return view('reports.usage', compact('rows', 'daily', 'totals', 'from', 'to', 'preset', 'groupBy'));
    }

    /**
     * Unified Purchase/Receive Report: everything received into stock —
     * from a supplier, or handed over internally by Store/Accounts —
     * over Today / This Week / This Month / a custom date range, with a
     * day-by-day breakdown and a split by source and by medicine.
     */
    public function receiveReport(Request $request)
    {
        [$from, $to, $preset] = $this->period($request);

        $purchases = Purchase::with(['supplier', 'items.medicine'])
            ->where('status', 'confirmed')
            ->whereBetween('purchase_date', [$from, $to])
            ->get();

        $bySource = $purchases->groupBy('source_type')->map(fn ($g, $type) => [
            'label' => match ($type) {
                'store' => 'Store',
                'accounts' => 'Accounts',
                default => 'Supplier',
            },
            'count' => $g->count(),
            'total' => $g->sum('grand_total'),
        ])->sortByDesc('total');

        $byMedicine = $purchases->flatMap->items->groupBy('medicine_id')->map(fn ($rows) => [
            'medicine' => $rows->first()->medicine,
            'quantity' => $rows->sum('base_unit_quantity'),
            'cost' => $rows->sum('line_total'),
        ])->sortByDesc('cost');

        $daily = $purchases->groupBy(fn ($p) => $p->purchase_date->toDateString())
            ->map(fn ($g, $date) => [
                'date' => $date,
                'count' => $g->count(),
                'total' => $g->sum('grand_total'),
            ])->sortKeys();

        $totals = [
            'count' => $purchases->count(),
            'total' => $purchases->sum('grand_total'),
            'paid' => $purchases->sum('payment_amount'),
            'outstanding' => $purchases->sum('outstanding_amount'),
        ];

        return view('reports.receive', compact('bySource', 'byMedicine', 'daily', 'totals', 'from', 'to', 'preset'));
    }

    // 9A. Purchase Expense Report
    public function purchaseExpense(Request $request)
    {
        [$from, $to] = $this->period($request);

        $purchases = Purchase::with(['supplier', 'items.medicine'])
            ->where('status', 'confirmed')
            ->whereBetween('purchase_date', [$from, $to])
            ->orderBy('purchase_date')->get();

        $bySupplier = $purchases->groupBy(fn ($p) => $p->sourceLabel())->map(fn ($rows) => [
            'label' => $rows->first()->sourceLabel(),
            'total' => $rows->sum('grand_total'),
        ])->sortByDesc('total');

        $byMedicine = $purchases->flatMap->items->groupBy('medicine_id')->map(fn ($rows) => [
            'medicine' => $rows->first()->medicine,
            'quantity' => $rows->sum('base_unit_quantity'),
            'cost' => $rows->sum('line_total'),
        ])->sortByDesc('cost');

        $totals = [
            'grand_total' => $purchases->sum('grand_total'),
            'paid' => $purchases->sum('payment_amount'),
            'outstanding' => $purchases->sum('outstanding_amount'),
            'discount' => $purchases->sum('discount_amount'),
            'tax' => $purchases->sum('tax_amount'),
        ];

        return view('reports.purchase-expense', compact('purchases', 'bySupplier', 'byMedicine', 'totals', 'from', 'to'));
    }

    // 9B. Employee-wise Medicine Expense Report
    public function employeeExpense(Request $request)
    {
        [$from, $to] = $this->period($request);
        $sort = $request->get('sort', 'cost');

        $rows = Employee::with(['department'])
            ->withCount(['issueVouchers' => fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to])])
            ->get()
            ->map(function ($employee) use ($from, $to) {
                $items = MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('employee_id', $employee->id)->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->with('medicine')->get();

                return [
                    'employee' => $employee,
                    'issue_count' => $employee->issue_vouchers_count,
                    'quantity' => $items->sum('base_unit_quantity'),
                    'cost' => $items->where('cost_unavailable', false)->sum('total_cost'),
                    'has_cost_unavailable' => $items->where('cost_unavailable', true)->isNotEmpty(),
                    'medicines' => $items->groupBy('medicine_id')->map(fn ($g) => ['medicine' => $g->first()->medicine, 'quantity' => $g->sum('base_unit_quantity'), 'cost' => $g->where('cost_unavailable', false)->sum('total_cost')]),
                ];
            })
            ->filter(fn ($r) => $r['issue_count'] > 0);

        $rows = match ($sort) {
            'quantity' => $rows->sortByDesc('quantity'),
            'employee' => $rows->sortBy(fn ($r) => $r['employee']->name),
            'department' => $rows->sortBy(fn ($r) => $r['employee']->department->name ?? ''),
            default => $rows->sortByDesc('cost'),
        };

        return view('reports.employee-expense', ['rows' => $rows, 'from' => $from, 'to' => $to, 'sort' => $sort]);
    }

    // 9C. Department-wise Expense Report
    public function departmentExpense(Request $request)
    {
        [$from, $to] = $this->period($request);

        $totalExpense = MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->where('cost_unavailable', false)->sum('total_cost');

        $rows = Department::with('employees')->get()->map(function ($dept) use ($from, $to, $totalExpense) {
            $vouchers = MedicineIssueVoucher::where('department_id', $dept->id)->where('status', 'posted')->whereBetween('issued_at', [$from, $to])->get();
            $items = MedicineIssueItem::whereIn('medicine_issue_voucher_id', $vouchers->pluck('id'))->with('medicine')->get();
            $cost = $items->where('cost_unavailable', false)->sum('total_cost');

            return [
                'department' => $dept,
                'employees_served' => $vouchers->pluck('employee_id')->unique()->count(),
                'quantity' => $items->sum('base_unit_quantity'),
                'cost' => $cost,
                'avg_per_employee' => $vouchers->pluck('employee_id')->unique()->count() > 0 ? $cost / $vouchers->pluck('employee_id')->unique()->count() : 0,
                'top_medicines' => $items->groupBy('medicine_id')->map(fn ($g) => ['medicine' => $g->first()->medicine, 'quantity' => $g->sum('base_unit_quantity')])->sortByDesc('quantity')->take(5),
                'pct_of_total' => $totalExpense > 0 ? ($cost / $totalExpense) * 100 : 0,
            ];
        })->filter(fn ($r) => $r['quantity'] > 0)->sortByDesc('cost');

        return view('reports.department-expense', compact('rows', 'from', 'to', 'totalExpense'));
    }

    // 9D. Medicine-wise Consumption Report
    public function medicineConsumption(Request $request)
    {
        [$from, $to] = $this->period($request);

        $rows = Medicine::with('category')->get()->map(function ($medicine) use ($from, $to) {
            $openingStock = $medicine->stockTransactions()->where('transaction_date', '<', $from)->get()->sum(fn ($t) => $t->quantity_in - $t->quantity_out);
            $received = $medicine->stockTransactions()->whereIn('transaction_type', ['purchase_receipt', 'opening'])->whereBetween('transaction_date', [$from, $to])->sum('quantity_in');
            $issued = $medicine->stockTransactions()->where('transaction_type', 'issue')->whereBetween('transaction_date', [$from, $to])->sum('quantity_out');
            $returnsAdj = $medicine->stockTransactions()->whereIn('transaction_type', ['return', 'adjustment_in', 'adjustment_out', 'damaged', 'expired'])->whereBetween('transaction_date', [$from, $to])->get()->sum(fn ($t) => $t->quantity_in - $t->quantity_out);
            $closing = $openingStock + $received + $returnsAdj - $issued;

            $purchaseCost = PurchaseItem::where('medicine_id', $medicine->id)->whereHas('purchase', fn ($q) => $q->where('status', 'confirmed')->whereBetween('purchase_date', [$from, $to]))->sum('line_total');
            $issueCost = MedicineIssueItem::where('medicine_id', $medicine->id)->where('cost_unavailable', false)->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->sum('total_cost');

            return [
                'medicine' => $medicine,
                'opening' => $openingStock,
                'received' => $received,
                'issued' => $issued,
                'returns_adjustments' => $returnsAdj,
                'closing' => $closing,
                'purchase_cost' => $purchaseCost,
                'issue_cost' => $issueCost,
                'avg_consumption' => $to->diffInDays($from) > 0 ? $issued / max(1, $to->diffInDays($from)) : $issued,
                'reorder_status' => $medicine->isLowStock() ? 'Reorder' : 'OK',
            ];
        });

        return view('reports.medicine-consumption', compact('rows', 'from', 'to'));
    }

    // 9E. Daily Medicine Usage Report (recreates the workbook's "Daily Medicine usage Report")
    public function dailyUsage(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $rows = Medicine::orderBy('name')->get()->map(function ($medicine) use ($date) {
            $opening = $medicine->stockTransactions()->where('transaction_date', '<', $date->toDateString())->get()->sum(fn ($t) => $t->quantity_in - $t->quantity_out);
            $received = $medicine->stockTransactions()->whereIn('transaction_type', ['purchase_receipt', 'opening'])->whereDate('transaction_date', $date)->sum('quantity_in');
            $issued = $medicine->stockTransactions()->where('transaction_type', 'issue')->whereDate('transaction_date', $date)->sum('quantity_out');

            return [
                'medicine' => $medicine,
                'opening' => $opening,
                'received' => $received,
                'total' => $opening + $received,
                'used' => $issued,
                'closing' => $opening + $received - $issued,
            ];
        });

        return view('reports.daily-usage', compact('rows', 'date'));
    }

    // 9F. Monthly Medicine Usage Report
    public function monthlyUsage(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $rows = Medicine::orderBy('name')->get()->map(function ($medicine) use ($from, $to) {
            $opening = $medicine->stockTransactions()->where('transaction_date', '<', $from->toDateString())->get()->sum(fn ($t) => $t->quantity_in - $t->quantity_out);
            $received = $medicine->stockTransactions()->whereIn('transaction_type', ['purchase_receipt', 'opening'])->whereBetween('transaction_date', [$from, $to])->sum('quantity_in');
            $issued = $medicine->stockTransactions()->where('transaction_type', 'issue')->whereBetween('transaction_date', [$from, $to])->sum('quantity_out');
            $purchaseCost = PurchaseItem::where('medicine_id', $medicine->id)->whereHas('purchase', fn ($q) => $q->where('status', 'confirmed')->whereBetween('purchase_date', [$from, $to]))->sum('line_total');
            $issueCost = MedicineIssueItem::where('medicine_id', $medicine->id)->where('cost_unavailable', false)->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->sum('total_cost');

            return [
                'medicine' => $medicine,
                'opening' => $opening,
                'received' => $received,
                'used' => $issued,
                'closing' => $opening + $received - $issued,
                'purchase_cost' => $purchaseCost,
                'issue_cost' => $issueCost,
            ];
        });

        return view('reports.monthly-usage', compact('rows', 'from', 'to', 'month', 'year'));
    }

    // 9G additional reports
    public function highestCostMedicines(Request $request)
    {
        [$from, $to] = $this->period($request);
        $rows = MedicineIssueItem::selectRaw('medicine_id, SUM(total_cost) as cost, SUM(base_unit_quantity) as qty')
            ->where('cost_unavailable', false)
            ->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))
            ->groupBy('medicine_id')->orderByDesc('cost')->with('medicine')->limit(25)->get();

        return view('reports.highest-cost-medicines', compact('rows', 'from', 'to'));
    }

    public function supplierPerformance(Request $request)
    {
        $suppliers = \App\Models\Supplier::with(['purchases' => fn ($q) => $q->where('status', 'confirmed')])->get()
            ->map(fn ($s) => [
                'supplier' => $s,
                'purchase_count' => $s->purchases->count(),
                'total_value' => $s->purchases->sum('grand_total'),
                'outstanding' => $s->purchases->sum('outstanding_amount'),
            ])->sortByDesc('total_value');

        return view('reports.supplier-performance', compact('suppliers'));
    }

    public function inventoryValuation()
    {
        $rows = Medicine::with('category')->get()->map(fn ($m) => [
            'medicine' => $m,
            'quantity' => $m->currentStock(),
            'value' => $m->currentStockValue(),
            'has_unpriced_stock' => $m->batches()->where('status', 'active')->where('cost_unavailable', true)->where('remaining_quantity', '>', 0)->exists(),
        ])->filter(fn ($r) => $r['quantity'] > 0);

        return view('reports.inventory-valuation', compact('rows'));
    }
}
