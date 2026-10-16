@extends('layouts.app')
@section('page-title', 'Purchase / Receive Report')
@section('content')
@include('reports._nav')
@include('reports._period-filter')

<x-card class="mb-6">
    <div class="grid grid-cols-4 gap-4 text-sm">
        <div><p class="text-slate-500">Receipts</p><p class="text-xl font-semibold text-navy-900">{{ $totals['count'] }}</p></div>
        <div><p class="text-slate-500">Total Value</p><p class="text-xl font-semibold text-navy-900">{{ config('erp.currency_symbol') }}{{ number_format($totals['total'], 2) }}</p></div>
        <div><p class="text-slate-500">Paid</p><p class="text-xl font-semibold text-navy-900">{{ config('erp.currency_symbol') }}{{ number_format($totals['paid'], 2) }}</p></div>
        <div><p class="text-slate-500">Outstanding</p><p class="text-xl font-semibold text-navy-900">{{ config('erp.currency_symbol') }}{{ number_format($totals['outstanding'], 2) }}</p></div>
    </div>
</x-card>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <x-card title="By Source">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Source</th><th class="py-2 pr-3">Receipts</th><th class="py-2 pr-3">Total</th></tr></thead>
            <tbody>
            @foreach($bySource as $row)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-3 font-medium">{{ $row['label'] }}</td>
                    <td class="py-2 pr-3">{{ $row['count'] }}</td>
                    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
            @if($bySource->isEmpty())<tr><td colspan="3" class="py-6 text-center text-slate-400">No receipts in this period.</td></tr>@endif
            </tbody>
        </table>
    </x-card>

    <x-card title="By Medicine">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Qty Received</th><th class="py-2 pr-3">Cost</th></tr></thead>
            <tbody>
            @foreach($byMedicine as $row)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-3 font-medium">{{ $row['medicine']->displayName() }}</td>
                    <td class="py-2 pr-3">{{ number_format($row['quantity'], 2) }}</td>
                    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['cost'], 2) }}</td>
                </tr>
            @endforeach
            @if($byMedicine->isEmpty())<tr><td colspan="3" class="py-6 text-center text-slate-400">No receipts in this period.</td></tr>@endif
            </tbody>
        </table>
    </x-card>
</div>

<x-card title="Day-by-Day Breakdown">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Receipts</th><th class="py-2 pr-3">Total Value</th></tr></thead>
        <tbody>
        @foreach($daily as $d)
            <tr class="border-b border-slate-50">
                <td class="py-2 pr-3">{{ \Carbon\Carbon::parse($d['date'])->format('d-m-Y') }}</td>
                <td class="py-2 pr-3">{{ $d['count'] }}</td>
                <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($d['total'], 2) }}</td>
            </tr>
        @endforeach
        @if($daily->isEmpty())<tr><td colspan="3" class="py-6 text-center text-slate-400">No activity in this period.</td></tr>@endif
        </tbody>
    </table>
</x-card>
@endsection
