@extends('layouts.app')
@section('page-title', 'Suppliers')
@section('content')
<div class="flex items-center justify-between mb-4">
    <form method="GET"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier..." class="rounded-lg border border-slate-300 text-sm px-3 py-2 w-64"></form>
    <a href="{{ route('suppliers.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-4 py-2 rounded-lg">+ New Supplier</a>
</div>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Contact</th><th class="py-2 pr-4">Phone</th><th class="py-2 pr-4">Purchase Value</th><th class="py-2 pr-4">Outstanding</th><th class="py-2 pr-4">Status</th><th></th></tr></thead>
        <tbody>
        @foreach($suppliers as $s)
        <tr class="border-b border-slate-50 hover:bg-slate-50">
            <td class="py-2 pr-4"><a href="{{ route('suppliers.show',$s) }}" class="font-medium text-navy-900 hover:underline">{{ $s->name }}</a></td>
            <td class="py-2 pr-4">{{ $s->contact_person }}</td>
            <td class="py-2 pr-4">{{ $s->phone }}</td>
            <td class="py-2 pr-4">{{ config('erp.currency_symbol') }}{{ number_format($s->totalPurchaseValue(),2) }}</td>
            <td class="py-2 pr-4">{{ config('erp.currency_symbol') }}{{ number_format($s->outstandingBalance(),2) }}</td>
            <td class="py-2 pr-4 capitalize">{{ $s->status }}</td>
            <td class="py-2 pr-4 text-right"><a href="{{ route('suppliers.edit',$s) }}" class="text-xs text-navy-700 hover:underline">Edit</a></td>
        </tr>
        @endforeach
        @if($suppliers->isEmpty())<tr><td colspan="7" class="py-6 text-center text-slate-400">No suppliers yet.</td></tr>@endif
        </tbody>
    </table>
    <div class="mt-4">{{ $suppliers->links() }}</div>
</x-card>
@endsection
