<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchases) {}

    public function index(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->source_type, fn ($q, $v) => $q->where('source_type', $v))
            ->when($request->supplier_id, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('purchase_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('purchase_date', '<=', $v))
            ->orderByDesc('purchase_date')->paginate(20)->withQueryString();

        return view('purchases.index', ['purchases' => $purchases, 'suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::where('status', 'active')->orderBy('name')->get(),
            'medicines' => Medicine::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'source_type' => 'required|in:supplier,store,accounts',
            'supplier_id' => 'required_if:source_type,supplier|nullable|exists:suppliers,id',
            'received_from' => 'nullable|string|max:191',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'required|string|max:100',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.purchase_quantity' => 'required|numeric|min:0.0001',
            'items.*.purchase_unit' => 'nullable|string|max:30',
            'items.*.free_quantity' => 'nullable|numeric|min:0',
            'items.*.unit_purchase_price' => 'required|numeric|min:0',
            'items.*.line_discount' => 'nullable|numeric|min:0',
            'items.*.line_tax' => 'nullable|numeric|min:0',
            'items.*.line_other_charges' => 'nullable|numeric|min:0',
        ]);

        try {
            $purchase = $this->purchases->createDraft([
                'purchase_number' => 'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'purchase_date' => $validated['purchase_date'],
                'source_type' => $validated['source_type'],
                'supplier_id' => $validated['source_type'] === 'supplier' ? $validated['supplier_id'] : null,
                'received_from' => $validated['source_type'] === 'supplier' ? null : ($validated['received_from'] ?? null),
                'supplier_invoice_number' => $validated['source_type'] === 'supplier' ? ($validated['supplier_invoice_number'] ?? null) : null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'other_charges' => $validated['other_charges'] ?? 0,
                'payment_amount' => $validated['payment_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ], $validated['items']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Receipt saved as draft. Confirm it to receive stock.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.medicine', 'receivedBy']);

        return view('purchases.show', ['purchase' => $purchase]);
    }

    public function confirm(Purchase $purchase)
    {
        try {
            $this->purchases->confirm($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase confirmed. Stock has been received into inventory.');
    }

    public function cancel(Purchase $purchase)
    {
        try {
            $this->purchases->cancel($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase cancelled.');
    }

    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.medicine']);

        return view('purchases.print', ['purchase' => $purchase]);
    }

    public function pdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.medicine']);
        $pdf = app('dompdf.wrapper')->loadView('purchases.print', ['purchase' => $purchase]);

        return $pdf->download("{$purchase->purchase_number}.pdf");
    }
}
