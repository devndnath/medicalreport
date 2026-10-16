<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Support\CurrentFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('department')
            ->when($request->search, fn ($q, $v) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$v}%")->orWhere('employee_code', 'like', "%{$v}%")))
            ->when($request->department_id, fn ($q, $v) => $q->where('department_id', $v))
            ->when($request->needs_id, fn ($q) => $q->where('needs_id_confirmation', true))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('employees.index', ['employees' => $employees, 'departments' => Department::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('employees.create', ['departments' => Department::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $employee = Employee::create($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee added.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'issueVouchers' => fn ($q) => $q->with('items.medicine')->orderByDesc('issued_at')]);

        return view('employees.show', ['employee' => $employee]);
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', ['employee' => $employee, 'departments' => Department::orderBy('name')->get()]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validateData($request, $employee->id);
        $employee->update($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee archived.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('employees', 'employee_code')
                    ->where('factory_id', CurrentFactory::id())
                    ->ignore($ignoreId),
            ],
            'name' => 'required|string|max:191',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:191',
            'employment_status' => 'required|in:active,inactive,resigned,terminated',
            'phone' => 'nullable|string|max:30',
            'joining_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);
    }
}
