@extends('layouts.app')
@section('page-title', 'Usage Report')
@section('content')
@include('reports._nav')

<div class="flex flex-wrap items-center justify-between gap-3 mb-2">
    <form method="GET" class="flex gap-2">
        @foreach(request()->except('group_by') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <select name="group_by" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
            @foreach(['employee' => 'By Employee', 'department' => 'By Department', 'medicine' => 'By Medicine'] as $k => $v)
                <option value="{{ $k }}" @selected($groupBy === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </form>
</div>

@include('reports._period-filter')

<x-card class="mb-6">
    <div class="grid grid-cols-3 gap-4 text-sm">
        <div><p class="text-slate-500">Total Issued Quantity</p><p class="text-xl font-semibold text-navy-900">{{ number_format($totals['quantity'], 2) }}</p></div>
        <div><p class="text-slate-500">Total Cost</p><p class="text-xl font-semibold text-navy-900">{{ config('erp.currency_symbol') }}{{ number_format($totals['cost'], 2) }}{{ $totals['has_cost_unavailable'] ? ' (partial)' : '' }}</p></div>
        <div><p class="text-slate-500">Issue Vouchers</p><p class="text-xl font-semibold text-navy-900">{{ $totals['voucher_count'] }}</p></div>
    </div>
</x-card>

<x-card :title="match($groupBy){'department'=>'By Department','medicine'=>'By Medicine',default=>'By Employee'}" class="mb-6">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b">
            <th class="py-2 pr-3">{{ match($groupBy){'department'=>'Department','medicine'=>'Medicine',default=>'Employee'} }}</th>
            @if($groupBy === 'employee')<th class="py-2 pr-3">Department</th>@endif
            <th class="py-2 pr-3">Vouchers</th>
            <th class="py-2 pr-3">Qty Issued</th>
            <th class="py-2 pr-3">Cost</th>
        </tr></thead>
        <tbody>
        @foreach($rows as $row)
            <tr class="border-b border-slate-50">
                <td class="py-2 pr-3 font-medium">{{ $row['label'] }}</td>
                @if($groupBy === 'employee')<td class="py-2 pr-3">{{ $row['sub_label'] ?? '—' }}</td>@endif
                <td class="py-2 pr-3">{{ $row['voucher_count'] }}</td>
                <td class="py-2 pr-3">{{ number_format($row['quantity'], 2) }}</td>
                <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['cost'], 2) }}{{ $row['has_cost_unavailable'] ? ' (partial)' : '' }}</td>
            </tr>
        @endforeach
        @if($rows->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">No medicine was issued in this period.</td></tr>@endif
        </tbody>
    </table>
</x-card>

<x-card title="Day-by-Day Breakdown">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Qty Issued</th><th class="py-2 pr-3">Cost</th></tr></thead>
        <tbody>
        @foreach($daily as $d)
            <tr class="border-b border-slate-50">
                <td class="py-2 pr-3">{{ \Carbon\Carbon::parse($d['date'])->format('d-m-Y') }}</td>
                <td class="py-2 pr-3">{{ number_format($d['quantity'], 2) }}</td>
                <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($d['cost'], 2) }}</td>
            </tr>
        @endforeach
        @if($daily->isEmpty())<tr><td colspan="3" class="py-6 text-center text-slate-400">No activity in this period.</td></tr>@endif
        </tbody>
    </table>
</x-card>
@endsection
