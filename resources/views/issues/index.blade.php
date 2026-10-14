@extends('layouts.app')
@section('page-title', 'Medicine Issue')
@section('content')
<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex gap-2">
        <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('issues.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-4 py-2 rounded-lg">+ Issue Medicine</a>
</div>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-4">Voucher #</th><th class="py-2 pr-4">Date</th><th class="py-2 pr-4">Employee</th><th class="py-2 pr-4">Department</th><th class="py-2 pr-4">Cost</th><th class="py-2 pr-4">Status</th></tr></thead>
        <tbody>
        @foreach($vouchers as $v)
        <tr class="border-b border-slate-50 hover:bg-slate-50">
            <td class="py-2 pr-4"><a href="{{ route('issues.show',$v) }}" class="font-medium text-navy-900 hover:underline">{{ $v->voucher_number }}</a></td>
            <td class="py-2 pr-4">{{ $v->issued_at->format('d-m-Y H:i') }}</td>
            <td class="py-2 pr-4">{{ $v->employee->name }} <span class="text-slate-400">({{ $v->employee->employee_code }})</span></td>
            <td class="py-2 pr-4">{{ $v->department->name ?? '—' }}</td>
            <td class="py-2 pr-4">{{ $v->has_cost_unavailable_lines ? 'Partial' : config('erp.currency_symbol').number_format($v->total_cost,2) }}</td>
            <td class="py-2 pr-4"><span class="px-2 py-0.5 rounded-full text-xs {{ $v->status==='posted' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $v->status }}</span></td>
        </tr>
        @endforeach
        @if($vouchers->isEmpty())<tr><td colspan="6" class="py-6 text-center text-slate-400">No vouchers yet.</td></tr>@endif
        </tbody>
    </table>
    <div class="mt-4">{{ $vouchers->links() }}</div>
</x-card>
@endsection
