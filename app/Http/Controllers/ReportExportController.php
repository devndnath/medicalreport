<?php

namespace App\Http\Controllers;

use App\Exports\ArrayReportExport;
use App\Models\Medicine;
use App\Models\MedicineIssueItem;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Excel/PDF export endpoints for the report suite. Kept separate from
 * ReportController so each report's screen controller stays focused on
 * building the rows; exporting re-runs the same query logic to guarantee
 * the file matches what was on screen.
 */
class ReportExportController extends Controller
{
    public function medicineConsumptionExcel(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to) : now()->endOfDay();

        $rows = Medicine::orderBy('name')->get()->map(function ($m) use ($from, $to) {
            $received = $m->stockTransactions()->whereIn('transaction_type', ['purchase_receipt', 'opening'])->whereBetween('transaction_date', [$from, $to])->sum('quantity_in');
            $issued = $m->stockTransactions()->where('transaction_type', 'issue')->whereBetween('transaction_date', [$from, $to])->sum('quantity_out');
            $purchaseCost = PurchaseItem::where('medicine_id', $m->id)->whereHas('purchase', fn ($q) => $q->where('status', 'confirmed')->whereBetween('purchase_date', [$from, $to]))->sum('line_total');
            $issueCost = MedicineIssueItem::where('medicine_id', $m->id)->where('cost_unavailable', false)->whereHas('voucher', fn ($q) => $q->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->sum('total_cost');

            return [$m->displayName(), $received, $issued, $purchaseCost, $issueCost];
        })->all();

        $export = new ArrayReportExport($rows, ['Medicine', 'Received', 'Issued', 'Purchase Cost', 'Issue Cost'], 'Medicine Consumption');

        return Excel::download($export, 'medicine-consumption-'.now()->format('Ymd').'.xlsx');
    }

    public function employeeExpensePdf(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to) : now()->endOfDay();

        $rows = \App\Models\Employee::with('department')->get()->map(function ($employee) use ($from, $to) {
            $items = MedicineIssueItem::whereHas('voucher', fn ($q) => $q->where('employee_id', $employee->id)->where('status', 'posted')->whereBetween('issued_at', [$from, $to]))->get();

            return [
                'employee' => $employee,
                'quantity' => $items->sum('base_unit_quantity'),
                'cost' => $items->where('cost_unavailable', false)->sum('total_cost'),
            ];
        })->filter(fn ($r) => $r['quantity'] > 0)->sortByDesc('cost');

        $pdf = app('dompdf.wrapper')->loadView('reports.employee-expense-pdf', ['rows' => $rows, 'from' => $from, 'to' => $to]);

        return $pdf->download('employee-expense-'.now()->format('Ymd').'.pdf');
    }
}
