@extends('layouts.app')
@section('page-title', 'Purchase Expense Report')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
</form>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <x-kpi-card label="Grand Total" value="{{ config('erp.currency_symbol') }}{{ number_format($totals['grand_total'],2) }}" />
    <x-kpi-card label="Paid" value="{{ config('erp.currency_symbol') }}{{ number_format($totals['paid'],2) }}" />
    <x-kpi-card label="Outstanding" value="{{ config('erp.currency_symbol') }}{{ number_format($totals['outstanding'],2) }}" />
    <x-kpi-card label="Discounts" value="{{ config('erp.currency_symbol') }}{{ number_format($totals['discount'],2) }}" />
    <x-kpi-card label="Tax" value="{{ config('erp.currency_symbol') }}{{ number_format($totals['tax'],2) }}" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-card title="Supplier-wise Totals">
        <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Supplier</th><th class="py-2">Total</th></tr></thead><tbody>
        @foreach($bySupplier as $row)<tr class="border-b border-slate-50"><td class="py-2">{{ $row['supplier']->name }}</td><td class="py-2">{{ config('erp.currency_symbol') }}{{ number_format($row['total'],2) }}</td></tr>@endforeach
        </tbody></table>
    </x-card>
    <x-card title="Medicine-wise Purchase Quantity & Cost">
        <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Medicine</th><th class="py-2">Qty</th><th class="py-2">Cost</th></tr></thead><tbody>
        @foreach($byMedicine as $row)<tr class="border-b border-slate-50"><td class="py-2">{{ $row['medicine']->displayName() }}</td><td class="py-2">{{ number_format($row['quantity'],2) }}</td><td class="py-2">{{ config('erp.currency_symbol') }}{{ number_format($row['cost'],2) }}</td></tr>@endforeach
        </tbody></table>
    </x-card>
</div>

<x-card title="Invoice-wise Purchase History">
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">PO #</th><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Supplier</th><th class="py-2 pr-3">Invoice #</th><th class="py-2 pr-3">Total</th><th class="py-2 pr-3">Paid</th><th class="py-2 pr-3">Outstanding</th></tr></thead>
<tbody>
@foreach($purchases as $p)
<tr class="border-b border-slate-50"><td class="py-2 pr-3">{{ $p->purchase_number }}</td><td class="py-2 pr-3">{{ $p->purchase_date->format('d-m-Y') }}</td><td class="py-2 pr-3">{{ $p->supplier->name }}</td><td class="py-2 pr-3">{{ $p->supplier_invoice_number }}</td><td class="py-2 pr-3">{{ number_format($p->grand_total,2) }}</td><td class="py-2 pr-3">{{ number_format($p->payment_amount,2) }}</td><td class="py-2 pr-3">{{ number_format($p->outstanding_amount,2) }}</td></tr>
@endforeach
@if($purchases->isEmpty())<tr><td colspan="7" class="py-6 text-center text-slate-400">No confirmed purchases in this period.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
