<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $medicines = Medicine::orderBy('name')->get();
        $medicine = $request->medicine_id ? Medicine::find($request->medicine_id) : null;

        $transactions = collect();
        if ($medicine) {
            $transactions = StockTransaction::where('medicine_id', $medicine->id)
                ->when($request->from, fn ($q, $v) => $q->whereDate('transaction_date', '>=', $v))
                ->when($request->to, fn ($q, $v) => $q->whereDate('transaction_date', '<=', $v))
                ->with('batch', 'user')
                ->orderBy('transaction_date')->orderBy('id')
                ->paginate(50)->withQueryString();
        }

        return view('stock.ledger', compact('medicines', 'medicine', 'transactions'));
    }

    public function lowStock()
    {
        $medicines = Medicine::where('status', 'active')->get()->filter(fn ($m) => $m->isLowStock());

        return view('stock.low-stock', ['medicines' => $medicines]);
    }

    public function expiry(Request $request)
    {
        $days = (int) $request->get('days', 90);
        $batches = \App\Models\MedicineBatch::with('medicine')
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::today()->addDays($days))
            ->orderBy('expiry_date')
            ->get();

        return view('stock.expiry', ['batches' => $batches, 'days' => $days]);
    }

    public function batches(Medicine $medicine)
    {
        $batches = $medicine->batches()->orderBy('expiry_date')->orderBy('received_date')->get();

        return view('stock.batches', compact('medicine', 'batches'));
    }
}
