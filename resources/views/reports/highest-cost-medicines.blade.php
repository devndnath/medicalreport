@extends('layouts.app')
@section('page-title', 'Highest-Cost Medicines')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">#</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Qty Issued</th><th class="py-2 pr-3">Total Cost</th></tr></thead>
<tbody>
@foreach($rows as $i => $row)
<tr class="border-b border-slate-50"><td class="py-2 pr-3">{{ $i+1 }}</td><td class="py-2 pr-3">{{ $row->medicine->displayName() }}</td><td class="py-2 pr-3">{{ number_format($row->qty,2) }}</td><td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row->cost,2) }}</td></tr>
@endforeach
@if($rows->isEmpty())<tr><td colspan="4" class="py-6 text-center text-slate-400">No data in this period.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
