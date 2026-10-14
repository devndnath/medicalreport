@extends('layouts.app')
@section('page-title', 'Purchases')
@section('content')
<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="">All statuses</option>
            @foreach(['draft','confirmed','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('purchases.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-4 py-2 rounded-lg">+ New Purchase</a>
</div>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-4">PO #</th><th class="py-2 pr-4">Date</th><th class="py-2 pr-4">Supplier</th><th class="py-2 pr-4">Total</th><th class="py-2 pr-4">Payment</th><th class="py-2 pr-4">Status</th><th></th></tr></thead>
        <tbody>
        @foreach($purchases as $p)
        <tr class="border-b border-slate-50 hover:bg-slate-50">
            <td class="py-2 pr-4"><a href="{{ route('purchases.show',$p) }}" class="font-medium text-navy-900 hover:underline">{{ $p->purchase_number }}</a></td>
            <td class="py-2 pr-4">{{ $p->purchase_date->format('d-m-Y') }}</td>
            <td class="py-2 pr-4">{{ $p->supplier->name }}</td>
            <td class="py-2 pr-4">{{ config('erp.currency_symbol') }}{{ number_format($p->grand_total,2) }}</td>
            <td class="py-2 pr-4 capitalize">{{ $p->payment_status }}</td>
            <td class="py-2 pr-4"><span class="px-2 py-0.5 rounded-full text-xs {{ $p->status==='confirmed' ? 'bg-emerald-50 text-emerald-700' : ($p->status==='cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ $p->status }}</span></td>
            <td class="py-2 pr-4 text-right"></td>
        </tr>
        @endforeach
        @if($purchases->isEmpty())<tr><td colspan="7" class="py-6 text-center text-slate-400">No purchases yet.</td></tr>@endif
        </tbody>
    </table>
    <div class="mt-4">{{ $purchases->links() }}</div>
</x-card>
@endsection
