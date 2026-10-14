@extends('layouts.app')
@section('page-title', $employee->name)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Profile">
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-slate-500">Employee ID</dt><dd class="font-mono">{{ $employee->employee_code }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Department</dt><dd>{{ $employee->department->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="capitalize">{{ $employee->employment_status }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Total issues</dt><dd>{{ $employee->issueVouchers->count() }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Total expense</dt><dd>{{ config('erp.currency_symbol') }}{{ number_format($employee->issueVouchers->sum('total_cost'),2) }}</dd></div>
        </dl>
        <a href="{{ route('employees.edit',$employee) }}" class="inline-block mt-4 text-sm text-navy-700 hover:underline">Edit employee →</a>
    </x-card>
    <x-card title="Medicine Issue History" class="lg:col-span-2">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Voucher</th><th class="py-2 pr-3">Medicines</th><th class="py-2 pr-3">Cost</th></tr></thead>
            <tbody>
            @foreach($employee->issueVouchers as $v)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-3">{{ $v->issued_at->format('d-m-Y H:i') }}</td>
                    <td class="py-2 pr-3"><a href="{{ route('issues.show',$v) }}" class="text-navy-700 hover:underline">{{ $v->voucher_number }}</a></td>
                    <td class="py-2 pr-3">{{ $v->items->map(fn($i)=>$i->medicine->displayName())->join(', ') }}</td>
                    <td class="py-2 pr-3">{{ $v->has_cost_unavailable_lines ? 'Partially unavailable' : config('erp.currency_symbol').number_format($v->total_cost,2) }}</td>
                </tr>
            @endforeach
            @if($employee->issueVouchers->isEmpty())<tr><td colspan="4" class="py-4 text-center text-slate-400">No issue history yet.</td></tr>@endif
            </tbody>
        </table>
    </x-card>
</div>
@endsection
