@extends('layouts.app')
@section('page-title', 'New Physical Count')
@section('content')
<x-card>
<form method="POST" action="{{ route('stock.counts.store') }}">
@csrf
<div class="mb-4"><label class="block text-xs text-slate-500 mb-1">Count Date *</label><input type="date" name="count_date" value="{{ now()->toDateString() }}" required class="rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Medicine</th><th class="py-2">System Qty</th><th class="py-2">Counted Qty</th></tr></thead>
<tbody>
@foreach($medicines as $i => $m)
<tr class="border-b border-slate-50">
    <td class="py-2">{{ $m['name'] }}<input type="hidden" name="lines[{{ $i }}][medicine_id]" value="{{ $m['id'] }}"></td>
    <td class="py-2">{{ number_format($m['system_quantity'],2) }}</td>
    <td class="py-2"><input type="number" step="0.0001" name="lines[{{ $i }}][counted_quantity]" value="{{ $m['system_quantity'] }}" class="rounded-lg border border-slate-300 text-sm px-2 py-1 w-32"></td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-6"><button class="bg-accent-500 text-white text-sm px-5 py-2.5 rounded-lg">Save Physical Count</button></div>
</form>
</x-card>
@endsection
