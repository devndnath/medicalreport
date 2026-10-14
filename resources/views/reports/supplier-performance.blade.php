@extends('layouts.app')
@section('page-title', 'Supplier Performance')
@section('content')
@include('reports._nav')
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Supplier</th><th class="py-2 pr-3">Purchases</th><th class="py-2 pr-3">Total Value</th><th class="py-2 pr-3">Outstanding</th></tr></thead>
<tbody>
@foreach($suppliers as $row)
<tr class="border-b border-slate-50"><td class="py-2 pr-3">{{ $row['supplier']->name }}</td><td class="py-2 pr-3">{{ $row['purchase_count'] }}</td><td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['total_value'],2) }}</td><td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['outstanding'],2) }}</td></tr>
@endforeach
</tbody>
</table>
</x-card>
@endsection
