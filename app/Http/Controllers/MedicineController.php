<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::with(['category', 'preferredSupplier'])
            ->when($request->search, fn ($q, $v) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$v}%")->orWhere('generic_name', 'like', "%{$v}%")->orWhere('sku', 'like', "%{$v}%")))
            ->when($request->category_id, fn ($q, $v) => $q->where('medicine_category_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderBy($request->get('sort', 'name'), $request->get('dir', 'asc'));

        $medicines = $query->paginate(20)->withQueryString();
        $categories = MedicineCategory::orderBy('name')->get();

        return view('medicines.index', compact('medicines', 'categories'));
    }

    public function create()
    {
        return view('medicines.create', [
            'categories' => MedicineCategory::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['sku'] = $data['sku'] ?? $this->generateSku($data['name']);
        $medicine = Medicine::create($data);

        return redirect()->route('medicines.show', $medicine)->with('success', 'Medicine created.');
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['category', 'preferredSupplier', 'batches' => fn ($q) => $q->orderByDesc('received_date')]);

        return view('medicines.show', ['medicine' => $medicine]);
    }

    public function edit(Medicine $medicine)
    {
        return view('medicines.edit', [
            'medicine' => $medicine,
            'categories' => MedicineCategory::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $this->validateData($request, $medicine->id);
        $medicine->update($data);

        return redirect()->route('medicines.show', $medicine)->with('success', 'Medicine updated.');
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->currentStock() > 0) {
            return back()->with('error', 'Cannot deactivate/delete a medicine that still has stock on hand. Adjust stock to zero first.');
        }
        $medicine->delete();

        return redirect()->route('medicines.index')->with('success', 'Medicine removed.');
    }

    public function openingStock(Request $request, Medicine $medicine, StockService $stock)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.0001',
            'batch_number' => 'nullable|string|max:100',
            'date' => 'nullable|date',
            'known_unit_cost' => 'nullable|numeric|min:0',
        ]);

        $stock->openingBalance(
            $medicine,
            (float) $data['quantity'],
            $data['batch_number'] ?? null,
            $data['date'] ?? null,
            costUnavailable: empty($data['known_unit_cost']),
            unitCost: $data['known_unit_cost'] ?? null,
        );

        return back()->with('success', 'Opening stock recorded.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:191',
            'generic_name' => 'nullable|string|max:191',
            'strength' => 'nullable|string|max:50',
            'dosage_form' => 'required|in:tablet,capsule,syrup,drop,gel,cream,liquid,injection,supply,other',
            'medicine_category_id' => 'nullable|exists:medicine_categories,id',
            'base_unit' => 'required|string|max:30',
            'purchase_unit' => 'nullable|string|max:30',
            'issue_unit' => 'nullable|string|max:30',
            'units_per_purchase_pack' => 'required|numeric|min:0.0001',
            'manufacturer' => 'nullable|string|max:191',
            'preferred_supplier_id' => 'nullable|exists:suppliers,id',
            'default_purchase_price' => 'nullable|numeric|min:0',
            'minimum_stock_level' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'storage_location' => 'nullable|string|max:191',
            'prescription_required' => 'boolean',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
            'sku' => 'nullable|string|max:50|unique:medicines,sku'.($ignoreId ? ",$ignoreId" : ''),
        ]);
    }

    protected function generateSku(string $name): string
    {
        $base = Str::upper(Str::slug(Str::substr($name, 0, 8), ''));

        do {
            $sku = $base.'-'.random_int(1000, 9999);
        } while (Medicine::where('sku', $sku)->exists());

        return $sku;
    }
}
