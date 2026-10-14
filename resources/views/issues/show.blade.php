@extends('layouts.app')
@section('page-title', $voucher->voucher_number)
@section('content')
<div class="flex items-center justify-between mb-4">
    <span class="px-2 py-1 rounded-full text-xs {{ $voucher->status==='posted' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ ucfirst($voucher->status) }}</span>
    <div class="flex gap-2">
        <a href="{{ route('issues.print',$voucher) }}" target="_blank" class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 hover:bg-slate-50">Print Voucher</a>
        @if(!$voucher->receiver_acknowledged)
        <form method="POST" action="{{ route('issues.acknowledge',$voucher) }}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="text-sm border border-slate-300 rounded-lg px-3 py-1.5">Mark Acknowledged</button></form>
        @endif
        @if($voucher->status === 'posted')
        <form method="POST" action="{{ route('issues.reverse',$voucher) }}" onsubmit="return confirm('Reverse this voucher and restore stock?')"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="text-sm border border-rose-300 text-rose-600 rounded-lg px-3 py-1.5">Reverse</button></form>
        @endif
    </div>
</div>

<x-card title="Voucher Details" class="mb-6">
    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><dt class="text-slate-500">Employee</dt><dd class="font-medium">{{ $voucher->employee->name }} ({{ $voucher->employee->employee_code }})</dd></div>
        <div><dt class="text-slate-500">Department</dt><dd>{{ $voucher->department->name ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">Date/Time</dt><dd>{{ $voucher->issued_at->format('d-m-Y H:i') }}</dd></div>
        <div><dt class="text-slate-500">Issued By</dt><dd>{{ $voucher->issuedBy->name ?? '—' }}</dd></div>
        <div class="md:col-span-4"><dt class="text-slate-500">Symptoms / Complaint</dt><dd>{{ $voucher->symptoms ?? '—' }}</dd></div>
    </dl>
</x-card>

<x-card title="Medicines Issued">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Unit Cost</th><th class="py-2 pr-3">Total Cost</th></tr></thead>
        <tbody>
        @foreach($voucher->items as $item)
            <tr class="border-b border-slate-50">
                <td class="py-2 pr-3">{{ $item->medicine->displayName() }}</td>
                <td class="py-2 pr-3">{{ number_format($item->quantity_issued,2) }} {{ $item->issue_unit }}</td>
                <td class="py-2 pr-3">{{ $item->cost_unavailable ? 'Cost unavailable' : config('erp.currency_symbol').number_format($item->unit_cost,4) }}</td>
                <td class="py-2 pr-3 font-medium">{{ $item->cost_unavailable ? '—' : config('erp.currency_symbol').number_format($item->total_cost,2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="3" class="pt-3 text-right font-semibold">Total</td><td class="pt-3 font-semibold">{{ $voucher->has_cost_unavailable_lines ? config('erp.currency_symbol').number_format($voucher->total_cost,2).' (partial — some lines cost unavailable)' : config('erp.currency_symbol').number_format($voucher->total_cost,2) }}</td></tr></tfoot>
    </table>
</x-card>
@endsection
