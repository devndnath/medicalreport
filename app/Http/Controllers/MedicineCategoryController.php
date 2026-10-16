<?php

namespace App\Http\Controllers;

use App\Models\MedicineCategory;
use App\Support\CurrentFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicineCategoryController extends Controller
{
    public function index()
    {
        return view('medicines.categories', ['categories' => MedicineCategory::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('medicine_categories', 'name')->where('factory_id', CurrentFactory::id())],
            'description' => 'nullable|string',
        ]);
        MedicineCategory::create($data);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, MedicineCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('medicine_categories', 'name')->where('factory_id', CurrentFactory::id())->ignore($category->id)],
            'description' => 'nullable|string',
        ]);
        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(MedicineCategory $category)
    {
        if ($category->medicines()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has medicines assigned.');
        }
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
