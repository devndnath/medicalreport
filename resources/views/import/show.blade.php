@extends('layouts.app')
@section('page-title', 'Import Preview')
@section('content')
<div class="flex items-center justify-between mb-4">
    <span class="px-2 py-1 rounded-full text-xs {{ $batch->status==='confirmed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($batch->status) }}</span>
    @if($batch->status === 'previewed')
    <div class="flex gap-2">
        <form method="POST" action="{{ route('import.confirm',$batch) }}" onsubmit="return confirm('Commit the valid rows to the database? This cannot be silently undone.')">
            @csrf<button class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">Confirm & Commit</button>
        </form>
        <form method="POST" action="{{ route('import.cancel',$batch) }}">
            @csrf<button class="border border-slate-300 text-sm px-4 py-2 rounded-lg">Cancel</button>
        </form>
    </div>
    @endif
</div>

<div class="grid grid-cols-4 gap-4 mb-6">
    <x-kpi-card label="Total Rows" value="{{ $batch->total_rows }}" />
    <x-kpi-card label="Valid" value="{{ $batch->valid_rows }}" tone="good" />
    <x-kpi-card label="Errors" value="{{ $batch->error_rows }}" tone="danger" />
    <x-kpi-card label="Duplicates" value="{{ $batch->duplicate_rows }}" tone="warn" />
</div>

<x-card title="Row-by-Row Preview">
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Sheet / Row</th><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Employee</th><th class="py-2 pr-3">Section</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Issues</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3 text-xs text-slate-400">{{ $row->source_sheet }} #{{ $row->source_row }}</td>
    <td class="py-2 pr-3">{{ $row->raw_data['date'] ?? '—' }}</td>
    <td class="py-2 pr-3">{{ $row->raw_data['name'] ?? '—' }} <span class="text-xs text-slate-400">{{ $row->raw_data['employee_code'] ?? '' }}</span></td>
    <td class="py-2 pr-3">{{ $row->raw_data['section'] ?? '—' }}</td>
    <td class="py-2 pr-3">{{ $row->raw_data['medicine_name'] ?? '—' }}</td>
    <td class="py-2 pr-3">{{ $row->raw_data['quantity'] ?? '—' }}</td>
    <td class="py-2 pr-3">
        <span class="px-2 py-0.5 rounded-full text-xs {{ $row->row_status==='valid' ? 'bg-emerald-50 text-emerald-700' : ($row->row_status==='error' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ $row->row_status }}</span>
    </td>
    <td class="py-2 pr-3 text-xs text-slate-500">{{ implode(' ', $row->errors ?? []) }}</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $rows->links() }}</div>
</x-card>
@endsection
