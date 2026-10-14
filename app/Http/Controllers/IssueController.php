<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineIssueVoucher;
use App\Services\IssueService;
use Illuminate\Http\Request;
use RuntimeException;

class IssueController extends Controller
{
    public function __construct(protected IssueService $issues) {}

    public function index(Request $request)
    {
        $vouchers = MedicineIssueVoucher::with(['employee', 'department'])
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->department_id, fn ($q, $v) => $q->where('department_id', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('issued_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('issued_at', '<=', $v))
            ->orderByDesc('issued_at')->paginate(25)->withQueryString();

        return view('issues.index', ['vouchers' => $vouchers]);
    }

    public function create()
    {
        return view('issues.create', [
            'employees' => Employee::where('employment_status', 'active')->orderBy('name')->get(),
            'medicines' => Medicine::where('status', 'active')->orderBy('name')->get()
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->displayName(), 'stock' => $m->currentStock(), 'unit' => $m->issue_unit ?? $m->base_unit]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'symptoms' => 'nullable|string',
            'remarks' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.medicine_id' => 'required|exists:medicines,id',
            'lines.*.quantity_issued' => 'required|numeric|min:0.0001',
            'lines.*.issue_unit' => 'nullable|string|max:30',
        ]);

        try {
            $employee = Employee::findOrFail($validated['employee_id']);
            $voucher = $this->issues->createVoucher($employee, $validated['lines'], $validated['symptoms'] ?? null, $validated['remarks'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('issues.show', $voucher)->with('success', 'Medicine issued and stock updated.');
    }

    public function show(MedicineIssueVoucher $voucher)
    {
        $voucher->load(['employee', 'department', 'items.medicine', 'issuedBy']);

        return view('issues.show', ['voucher' => $voucher]);
    }

    public function print(MedicineIssueVoucher $voucher)
    {
        $voucher->load(['employee', 'department', 'items.medicine']);

        return view('issues.print', ['voucher' => $voucher]);
    }

    public function reverse(MedicineIssueVoucher $voucher)
    {
        try {
            $this->issues->reverseVoucher($voucher);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Voucher reversed and stock restored. Issue a new voucher if a correction needs reissuing.');
    }

    public function acknowledge(MedicineIssueVoucher $voucher)
    {
        $voucher->update(['receiver_acknowledged' => true]);

        return back()->with('success', 'Receiver acknowledgment recorded.');
    }
}
