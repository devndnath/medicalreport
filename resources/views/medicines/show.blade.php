@extends('layouts.app')
@section('page-title', $medicine->displayName())
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Details" class="lg:col-span-1">
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-slate-500">SKU</dt><dd class="font-mono">{{ $medicine->sku }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Dosage form</dt><dd class="capitalize">{{ $medicine->dosage_form }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Category</dt><dd>{{ $medicine->category->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Base unit</dt><dd>{{ $medicine->base_unit }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Current stock</dt><dd class="font-semibold {{ $medicine->isLowStock() ? 'text-amber-600' : '' }}">{{ number_format($medicine->currentStock(),2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Stock value</dt><dd>{{ config('erp.currency_symbol') }}{{ number_format($medicine->currentStockValue(),2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Reorder level</dt><dd>{{ number_format($medicine->reorder_level,2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="capitalize">{{ $medicine->status }}</dd></div>
        </dl>
        <a href="{{ route('medicines.edit',$medicine) }}" class="inline-block mt-4 text-sm text-navy-700 hover:underline">Edit medicine →</a>
        <a href="{{ route('stock.ledger', ['medicine_id'=>$medicine->id]) }}" class="inline-block mt-2 text-sm text-navy-700 hover:underline">View stock ledger →</a>
    </x-card>

    <x-card title="Record Opening Stock" class="lg:col-span-2">
        <form method="POST" action="{{ route('medicines.opening-stock',$medicine) }}" class="grid grid-cols-2 gap-3">
            @csrf
            <div><label class="block text-xs text-slate-500 mb-1">Quantity *</label><input type="number" step="0.0001" name="quantity" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
            <div><label class="block text-xs text-slate-500 mb-1">Date</label><input type="date" name="date" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
            <div><label class="block text-xs text-slate-500 mb-1">Batch Number</label><input name="batch_number" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
            <div><label class="block text-xs text-slate-500 mb-1">Known unit cost (leave blank if unknown)</label><input type="number" step="0.000001" name="known_unit_cost" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
            <div class="col-span-2"><button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Post Opening Stock</button></div>
        </form>

        <h4 class="text-sm font-semibold text-slate-700 mt-6 mb-2">Batches</h4>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Batch</th><th class="py-2 pr-3">Received</th><th class="py-2 pr-3">Expiry</th><th class="py-2 pr-3">Remaining</th><th class="py-2 pr-3">Unit Cost</th><th class="py-2 pr-3">Status</th></tr></thead>
            <tbody>
            @foreach($medicine->batches as $b)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-3">{{ $b->batch_number }}</td>
                    <td class="py-2 pr-3">{{ $b->received_date->format('d-m-Y') }}</td>
                    <td class="py-2 pr-3">{{ $b->expiry_date?->format('d-m-Y') ?? '—' }}</td>
                    <td class="py-2 pr-3">{{ number_format($b->remaining_quantity,2) }}</td>
                    <td class="py-2 pr-3">{{ $b->cost_unavailable ? 'Cost unavailable' : config('erp.currency_symbol').number_format($b->unit_cost,4) }}</td>
                    <td class="py-2 pr-3 capitalize">{{ $b->status }}</td>
                </tr>
            @endforeach
            @if($medicine->batches->isEmpty())<tr><td colspan="6" class="py-4 text-center text-slate-400">No batches yet.</td></tr>@endif
            </tbody>
        </table>
        </div>
    </x-card>
</div>
@endsection
