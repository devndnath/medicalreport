@extends('layouts.app')
@section('page-title', $purchase->purchase_number)
@section('content')
<div class="flex items-center justify-between mb-4">
    <span class="px-2 py-1 rounded-full text-xs {{ $purchase->status==='confirmed' ? 'bg-emerald-50 text-emerald-700' : ($purchase->status==='cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($purchase->status) }}</span>
    <div class="flex gap-2">
        <a href="{{ route('purchases.print',$purchase) }}" target="_blank" class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 hover:bg-slate-50">Print</a>
        <a href="{{ route('purchases.pdf',$purchase) }}" class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 hover:bg-slate-50">Download PDF</a>
        @if($purchase->status === 'draft')
            <form method="POST" action="{{ route('purchases.confirm',$purchase) }}" onsubmit="return confirm('Confirm this purchase and receive stock?')"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="text-sm bg-accent-500 text-white rounded-lg px-3 py-1.5">Confirm & Receive Stock</button></form>
            <form method="POST" action="{{ route('purchases.cancel',$purchase) }}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="text-sm border border-rose-300 text-rose-600 rounded-lg px-3 py-1.5">Cancel</button></form>
        @endif
    </div>
</div>

<x-card title="Purchase Details" class="mb-6">
    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><dt class="text-slate-500">Supplier</dt><dd class="font-medium">{{ $purchase->supplier->name }}</dd></div>
        <div><dt class="text-slate-500">Date</dt><dd>{{ $purchase->purchase_date->format('d-m-Y') }}</dd></div>
        <div><dt class="text-slate-500">Invoice #</dt><dd>{{ $purchase->supplier_invoice_number ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Received by</dt><dd>{{ $purchase->receivedBy->name ?? '—' }}</dd></div>
    </dl>
</x-card>

<x-card title="Line Items">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Batch</th><th class="py-2 pr-3">Expiry</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Unit Price</th><th class="py-2 pr-3">Line Total</th></tr></thead>
        <tbody>
        @foreach($purchase->items as $item)
            <tr class="border-b border-slate-50">
                <td class="py-2 pr-3">{{ $item->medicine->displayName() }}</td>
                <td class="py-2 pr-3">{{ $item->batch_number }}</td>
                <td class="py-2 pr-3">{{ $item->expiry_date?->format('d-m-Y') ?? '—' }}</td>
                <td class="py-2 pr-3">{{ number_format($item->purchase_quantity,2) }} {{ $item->purchase_unit }}</td>
                <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($item->unit_purchase_price,4) }}</td>
                <td class="py-2 pr-3 font-medium">{{ config('erp.currency_symbol') }}{{ number_format($item->line_total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="pt-3 text-right text-slate-500">Discount</td><td class="pt-3">{{ config('erp.currency_symbol') }}{{ number_format($purchase->discount_amount,2) }}</td></tr>
            <tr><td colspan="5" class="text-right text-slate-500">Tax/VAT</td><td>{{ config('erp.currency_symbol') }}{{ number_format($purchase->tax_amount,2) }}</td></tr>
            <tr><td colspan="5" class="text-right text-slate-500">Other Charges</td><td>{{ config('erp.currency_symbol') }}{{ number_format($purchase->other_charges,2) }}</td></tr>
            <tr><td colspan="5" class="text-right font-semibold">Grand Total</td><td class="font-semibold">{{ config('erp.currency_symbol') }}{{ number_format($purchase->grand_total,2) }}</td></tr>
            <tr><td colspan="5" class="text-right text-slate-500">Paid</td><td>{{ config('erp.currency_symbol') }}{{ number_format($purchase->payment_amount,2) }}</td></tr>
            <tr><td colspan="5" class="text-right text-slate-500">Outstanding</td><td>{{ config('erp.currency_symbol') }}{{ number_format($purchase->outstanding_amount,2) }}</td></tr>
        </tfoot>
    </table>
</x-card>
@endsection
