@extends('layouts.app')
@section('page-title', $supplier->name)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Details">
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-slate-500">Contact</dt><dd>{{ $supplier->contact_person }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ $supplier->phone }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $supplier->email }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Total Purchases</dt><dd>{{ config('erp.currency_symbol') }}{{ number_format($supplier->totalPurchaseValue(),2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Outstanding</dt><dd>{{ config('erp.currency_symbol') }}{{ number_format($supplier->outstandingBalance(),2) }}</dd></div>
        </dl>
        <a href="{{ route('suppliers.edit',$supplier) }}" class="inline-block mt-4 text-sm text-navy-700 hover:underline">Edit supplier →</a>
    </x-card>
    <x-card title="Purchase History" class="lg:col-span-2">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">PO #</th><th class="py-2 pr-3">Invoice</th><th class="py-2 pr-3">Total</th><th class="py-2 pr-3">Status</th></tr></thead>
            <tbody>
            @foreach($supplier->purchases as $p)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-3">{{ $p->purchase_date->format('d-m-Y') }}</td>
                    <td class="py-2 pr-3"><a href="{{ route('purchases.show',$p) }}" class="text-navy-700 hover:underline">{{ $p->purchase_number }}</a></td>
                    <td class="py-2 pr-3">{{ $p->supplier_invoice_number }}</td>
                    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($p->grand_total,2) }}</td>
                    <td class="py-2 pr-3 capitalize">{{ $p->status }}</td>
                </tr>
            @endforeach
            @if($supplier->purchases->isEmpty())<tr><td colspan="5" class="py-4 text-center text-slate-400">No purchases yet.</td></tr>@endif
            </tbody>
        </table>
    </x-card>
</div>
@endsection
