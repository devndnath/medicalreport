@extends('layouts.app')
@section('page-title', 'Daily Medicine Usage Report')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">SL</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Op. Balance</th><th class="py-2 pr-3">Received</th><th class="py-2 pr-3">Total</th><th class="py-2 pr-3">Used</th><th class="py-2 pr-3">P. Balance</th></tr></thead>
<tbody>
@foreach($rows as $i => $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $i+1 }}</td>
    <td class="py-2 pr-3">{{ $row['medicine']->displayName() }}</td>
    <td class="py-2 pr-3">{{ number_format($row['opening'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['received'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['total'],2) }}</td>
    <td class="py-2 pr-3">{{ number_format($row['used'],2) }}</td>
    <td class="py-2 pr-3 font-medium">{{ number_format($row['closing'],2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</x-card>
@endsection
