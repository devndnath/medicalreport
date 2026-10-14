<?php

namespace App\Http\Controllers;

use App\Models\MedicineBatch;
use App\Models\MedicineIssueItem;
use App\Models\MedicineReturn;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MedicineReturnController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $returns = MedicineReturn::with(['medicine', 'batch'])->orderByDesc('return_date')->paginate(25);

        return view('returns.index', ['returns' => $returns]);
    }

    public function create()
    {
        return view('returns.create', ['issueItems' => MedicineIssueItem::with(['medicine', 'voucher.employee'])->whereHas('voucher', fn ($q) => $q->where('status', 'posted'))->orderByDesc('id')->limit(100)->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_issue_item_id' => 'required|exists:medicine_issue_items,id',
            'quantity' => 'required|numeric|min:0.0001',
            'condition' => 'required|in:usable,damaged',
            'reason' => 'nullable|string',
        ]);

        $issueItem = MedicineIssueItem::with('batchConsumptions')->findOrFail($data['medicine_issue_item_id']);
        $batchConsumption = $issueItem->batchConsumptions()->first();
        $batch = $batchConsumption ? MedicineBatch::find($batchConsumption->medicine_batch_id) : null;

        $return = MedicineReturn::create([
            'return_number' => 'RET-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'return_date' => now()->toDateString(),
            'medicine_issue_item_id' => $issueItem->id,
            'medicine_id' => $issueItem->medicine_id,
            'medicine_batch_id' => $batch?->id,
            'quantity' => $data['quantity'],
            'condition' => $data['condition'],
            'received_by' => Auth::id(),
            'reason' => $data['reason'] ?? null,
        ]);

        if ($batch) {
            $this->stock->returnToBatch($batch, (float) $data['quantity'], $data['condition'], $return);
        }

        return redirect()->route('returns.index')->with('success', 'Return recorded.');
    }
}
