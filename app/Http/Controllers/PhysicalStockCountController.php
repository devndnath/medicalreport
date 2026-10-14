<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PhysicalStockCount;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PhysicalStockCountController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $counts = PhysicalStockCount::withCount('items')->orderByDesc('count_date')->paginate(20);

        return view('stock.counts-index', ['counts' => $counts]);
    }

    public function create()
    {
        $medicines = Medicine::where('status', 'active')->orderBy('name')->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->displayName(), 'system_quantity' => $m->currentStock()]);

        return view('stock.counts-create', ['medicines' => $medicines]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'count_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.medicine_id' => 'required|exists:medicines,id',
            'lines.*.counted_quantity' => 'required|numeric|min:0',
        ]);

        $count = DB::transaction(function () use ($data) {
            $count = PhysicalStockCount::create([
                'count_number' => 'PSC-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'count_date' => $data['count_date'],
                'counted_by' => Auth::id(),
                'status' => 'in_progress',
            ]);

            foreach ($data['lines'] as $line) {
                $medicine = Medicine::findOrFail($line['medicine_id']);
                $system = $medicine->currentStock();
                $count->items()->create([
                    'medicine_id' => $medicine->id,
                    'system_quantity' => $system,
                    'counted_quantity' => $line['counted_quantity'],
                    'discrepancy' => $line['counted_quantity'] - $system,
                ]);
            }

            return $count;
        });

        return redirect()->route('stock.counts.show', $count)->with('success', 'Physical count recorded. Review discrepancies and reconcile.');
    }

    public function show(PhysicalStockCount $count)
    {
        $count->load('items.medicine');

        return view('stock.counts-show', ['count' => $count]);
    }

    public function reconcile(PhysicalStockCount $count)
    {
        DB::transaction(function () use ($count) {
            foreach ($count->items as $item) {
                if (abs($item->discrepancy) < 0.0001) {
                    continue;
                }

                $direction = $item->discrepancy > 0 ? 'in' : 'out';
                $this->stock->adjust(
                    $item->medicine,
                    null,
                    $direction,
                    abs($item->discrepancy),
                    'physical_count_correction',
                    $count,
                    "Reconciled from physical count {$count->count_number}"
                );
            }

            $count->status = 'reconciled';
            $count->save();
        });

        return back()->with('success', 'Stock reconciled to the physical count.');
    }
}
