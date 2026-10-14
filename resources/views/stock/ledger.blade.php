@extends('layouts.app')
@section('page-title', 'Stock Ledger')
@section('content')
<form method="GET" class="flex gap-2 mb-4">
    <select name="medicine_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-3 py-2 w-72">
        <option value="">Select medicine</option>
        @foreach($medicines as $m)<option value="{{ $m->id }}" @selected($medicine && $medicine->id==$m->id)>{{ $m->displayName() }}</option>@endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
</form>

<x-card>
@if($medicine)
<table class="w-full text-sm">
    <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Type</th><th class="py-2 pr-3">Batch</th><th class="py-2 pr-3">In</th><th class="py-2 pr-3">Out</th><th class="py-2 pr-3">Balance</th><th class="py-2 pr-3">Unit Cost</th><th class="py-2 pr-3">Value</th><th class="py-2 pr-3">User</th></tr></thead>
    <tbody>
    @foreach($transactions as $t)
        <tr class="border-b border-slate-50">
            <td class="py-2 pr-3">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d-m-Y') }}</td>
            <td class="py-2 pr-3 capitalize">{{ str_replace('_',' ',$t->transaction_type) }}</td>
            <td class="py-2 pr-3">{{ $t->batch->batch_number ?? '—' }}</td>
            <td class="py-2 pr-3">{{ $t->quantity_in > 0 ? number_format($t->quantity_in,2) : '—' }}</td>
            <td class="py-2 pr-3">{{ $t->quantity_out > 0 ? number_format($t->quantity_out,2) : '—' }}</td>
            <td class="py-2 pr-3 font-medium">{{ number_format($t->running_balance,2) }}</td>
            <td class="py-2 pr-3">{{ $t->cost_unavailable ? 'N/A' : number_format($t->unit_cost,4) }}</td>
            <td class="py-2 pr-3">{{ $t->cost_unavailable ? 'N/A' : number_format($t->transaction_value,2) }}</td>
            <td class="py-2 pr-3">{{ $t->user->name ?? '—' }}</td>
        </tr>
    @endforeach
    @if($transactions->isEmpty())<tr><td colspan="9" class="py-6 text-center text-slate-400">No transactions in this range.</td></tr>@endif
    </tbody>
</table>
<div class="mt-4">{{ $transactions->links() }}</div>
@else
<p class="text-sm text-slate-500">Select a medicine to view its ledger.</p>
@endif
</x-card>
@endsection
