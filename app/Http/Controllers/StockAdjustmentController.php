<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $adjustments = StockAdjustment::with(['medicine', 'batch', 'approvedBy'])->orderByDesc('adjustment_date')->paginate(25);

        return view('adjustments.index', ['adjustments' => $adjustments]);
    }

    public function create()
    {
        return view('adjustments.create', ['medicines' => Medicine::where('status', 'active')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'medicine_batch_id' => 'nullable|exists:medicine_batches,id',
            'direction' => 'required|in:in,out',
            'reason' => 'required|in:damaged,expired,physical_count_correction,data_correction,other',
            'quantity' => 'required|numeric|min:0.0001',
            'remarks' => 'nullable|string',
        ]);

        $medicine = Medicine::findOrFail($data['medicine_id']);
        $batch = $data['medicine_batch_id'] ?? null ? \App\Models\MedicineBatch::find($data['medicine_batch_id']) : null;

        try {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => 'ADJ-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'adjustment_date' => now()->toDateString(),
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch?->id,
                'direction' => $data['direction'],
                'reason' => $data['reason'],
                'quantity' => $data['quantity'],
                'approved_by' => Auth::id(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            $this->stock->adjust($medicine, $batch, $data['direction'], (float) $data['quantity'], $data['reason'], $adjustment, $data['remarks'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('adjustments.index')->with('success', 'Stock adjustment posted.');
    }
}
