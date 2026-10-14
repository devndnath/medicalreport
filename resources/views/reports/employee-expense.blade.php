@extends('layouts.app')
@section('page-title', 'Employee-wise Medicine Expense')
@section('content')
@include('reports._nav')
<form method="GET" class="flex gap-2 mb-6">
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <select name="sort" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        @foreach(['cost'=>'Highest Expense','quantity'=>'Highest Quantity','employee'=>'Employee Name','department'=>'Department'] as $k=>$v)
        <option value="{{ $k }}" @selected($sort===$k)>{{ $v }}</option>
        @endforeach
    </select>
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
    <a href="{{ route('reports.employee-expense.export-pdf', request()->query()) }}" class="ml-auto text-sm border border-slate-300 rounded-lg px-4 py-2 hover:bg-slate-50">Export PDF</a>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Employee</th><th class="py-2 pr-3">Department</th><th class="py-2 pr-3">Issues</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Cost</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3"><a href="{{ route('employees.show',$row['employee']) }}" class="text-navy-900 hover:underline">{{ $row['employee']->name }}</a></td>
    <td class="py-2 pr-3">{{ $row['employee']->department->name ?? '—' }}</td>
    <td class="py-2 pr-3">{{ $row['issue_count'] }}</td>
    <td class="py-2 pr-3">{{ number_format($row['quantity'],2) }}</td>
    <td class="py-2 pr-3">{{ config('erp.currency_symbol') }}{{ number_format($row['cost'],2) }}{{ $row['has_cost_unavailable'] ? ' (partial)' : '' }}</td>
</tr>
@endforeach
@if($rows->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">No issues in this period.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
