@extends('layouts.app')
@section('page-title', 'Monthly Medicine Usage Report')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <select name="month" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        @foreach(range(1,12) as $m)<option value="{{ $m }}" @selected($month==$m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>@endforeach
    </select>
    <input type="number" name="year" value="{{ $year }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2 w-24">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">SL</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Opening</th><th class="py-2 pr-3">Received</th><th class="py-2 pr-3">Used</th><th class="py-2 pr-3">Closing</th><th class="py-2 pr-3">Purchase Cost</th><th class="py-2 pr-3">Issue Cost</th></tr></thead>
<tbody>
@foreach($rows as $i => $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $i+1 }}</td>
    <td class="py-2 pr-3">{{ $row['medicine']->displayName() }}</td>
    <td class="py-2 pr-3">{{ number_format($row['opening'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['received'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['used'],2) }}</td>
    <td class="py-2 pr-3 font-medium">{{ number_format($row['closing'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['purchase_cost'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['issue_cost'],2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</x-card>
@endsection
