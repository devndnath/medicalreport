<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => fn ($q) => $q->orderByDesc('purchase_date')]);

        return view('suppliers.show', ['supplier' => $supplier]);
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateData($request);
        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Cannot delete a supplier with purchase history. Mark it inactive instead.');
        }
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
            'business_details' => 'nullable|string|max:191',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
        ]);
    }
}
