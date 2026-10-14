<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('departments.index', ['departments' => Department::withCount('employees')->orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:departments,name',
            'legacy_label' => 'nullable|string|max:191',
            'remarks' => 'nullable|string',
        ]);
        Department::create($data);

        return back()->with('success', 'Department added.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:departments,name,'.$department->id,
            'legacy_label' => 'nullable|string|max:191',
            'is_active' => 'boolean',
            'remarks' => 'nullable|string',
        ]);
        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete a department with employees assigned.');
        }
        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
