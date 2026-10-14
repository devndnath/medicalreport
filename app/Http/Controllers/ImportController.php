<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\LegacyWorkbookImportService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function __construct(protected LegacyWorkbookImportService $importer) {}

    public function create()
    {
        return view('import.create', ['batches' => ImportBatch::orderByDesc('created_at')->limit(10)->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'workbook' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('workbook');
        // The original workbook is stored untouched under a unique name —
        // never overwritten, never edited in place.
        $storedPath = $file->store('legacy-imports');

        $batch = $this->importer->preview($storedPath, $file->getClientOriginalName());

        return redirect()->route('import.show', $batch)->with('success', 'Workbook parsed. Review the preview below before confirming.');
    }

    public function show(ImportBatch $batch)
    {
        $rows = $batch->rows()->orderBy('source_row')->paginate(50);

        return view('import.show', ['batch' => $batch, 'rows' => $rows]);
    }

    public function confirm(ImportBatch $batch, StockService $stock)
    {
        if ($batch->status === 'confirmed') {
            return back()->with('error', 'This batch has already been committed.');
        }

        $result = $this->importer->commit($batch, $stock);

        return redirect()->route('import.show', $batch)->with(
            'success',
            "Import committed — departments: {$result['departments']}, employees: {$result['employees']}, medicines: {$result['medicines']}, issues posted: {$result['issues']}, skipped: {$result['skipped']}."
        );
    }

    public function cancel(ImportBatch $batch)
    {
        $batch->update(['status' => 'cancelled']);

        return redirect()->route('import.create')->with('success', 'Import batch cancelled. No data was committed.');
    }
}
