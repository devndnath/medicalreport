@extends('layouts.app')
@section('page-title', 'Inventory Valuation')
@section('content')
@include('reports._nav')
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Category</th><th class="py-2 pr-3">Quantity</th><th class="py-2 pr-3">Value (FIFO)</th><th class="py-2 pr-3">Notes</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $row['medicine']->displayName() }}</td>
    <td class="py-2 pr-3">{{ $row['medicine']->category->name ?? '—' }}</td>
    <td class="py-2 pr-3">{{ number_format($row['quantity'],2) }}</td>
    <td class="py-2 pr-3 font-medium">{{ config('erp.currency_symbol') }}{{ number_format($row['value'],2) }}</td>
    <td class="py-2 pr-3 text-xs text-amber-600">{{ $row['has_unpriced_stock'] ? 'Includes unpriced legacy stock' : '' }}</td>
</tr>
@endforeach
</tbody>
<tfoot><tr><td colspan="3" class="pt-3 text-right font-semibold">Total Inventory Value</td><td class="pt-3 font-semibold">{{ config('erp.currency_symbol') }}{{ number_format($rows->sum('value'),2) }}</td><td></td></tr></tfoot>
</table>
</x-card>
@endsection
