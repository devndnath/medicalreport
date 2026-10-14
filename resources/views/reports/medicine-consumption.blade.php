@extends('layouts.app')
@section('page-title', 'Medicine-wise Consumption')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
    <a href="{{ route('reports.medicine-consumption.export-excel', request()->query()) }}" class="ml-auto text-sm border border-slate-300 rounded-lg px-4 py-2 hover:bg-slate-50">Export Excel</a>
</form>
<x-card>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Opening</th><th class="py-2 pr-3">Received</th><th class="py-2 pr-3">Issued</th><th class="py-2 pr-3">Ret/Adj</th><th class="py-2 pr-3">Closing</th><th class="py-2 pr-3">Purchase Cost</th><th class="py-2 pr-3">Issue Cost</th><th class="py-2 pr-3">Avg/Day</th><th class="py-2 pr-3">Reorder</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $row['medicine']->displayName() }}</td>
    <td class="py-2 pr-3">{{ number_format($row['opening'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['received'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['issued'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['returns_adjustments'],2) }}</td>
    <td class="py-2 pr-3 font-medium">{{ number_format($row['closing'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['purchase_cost'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['issue_cost'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['avg_consumption'],2) }}</td>
    <td class="py-2 pr-3">@if($row['reorder_status']==='Reorder')<span class="text-amber-600 font-medium">Reorder</span>@else OK @endif</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</x-card>
@endsection
