@extends('layouts.app')
@section('page-title', 'Department-wise Expense')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Department</th><th class="py-2 pr-3">Employees Served</th><th class="py-2 pr-3">Qty Issued</th><th class="py-2 pr-3">Expense</th><th class="py-2 pr-3">Avg / Employee</th><th class="py-2 pr-3">% of Total</th><th class="py-2 pr-3">Top Medicines</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $row['department']->name ?? 'Unassigned' }}</td>
    <td class="py-2 pr-3">{{ $row['employees_served'] }}</td>
    <td class="py-2 pr-3">{{ number_format($row['quantity'],2) }}</td>
    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['cost'],2) }}</td>
    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['avg_per_employee'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['pct_of_total'],1) }}%</td>
    <td class="py-2 pr-3 text-xs text-slate-500">{{ $row['top_medicines']->map(fn($m)=>$m['medicine']->displayName())->join(', ') }}</td>
</tr>
@endforeach
@if($rows->isEmpty())<tr><td colspan="7" class="py-6 text-center text-slate-400">No department activity in this period.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
