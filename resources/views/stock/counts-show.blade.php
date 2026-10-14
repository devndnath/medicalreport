@extends('layouts.app')
@section('page-title', $count->count_number)
@section('content')
<div class="flex justify-between items-center mb-4">
    <span class="px-2 py-1 rounded-full text-xs {{ $count->status==='reconciled' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($count->status) }}</span>
    @if($count->status === 'in_progress')
    <form method="POST" action="{{ route('stock.counts.reconcile',$count) }}" onsubmit="return confirm('Post stock adjustments to match this physical count?')">
        @csrf<button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Reconcile Stock</button>
    </form>
    @endif
</div>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">System Qty</th><th class="py-2 pr-3">Counted Qty</th><th class="py-2 pr-3">Discrepancy</th></tr></thead>
<tbody>
@foreach($count->items as $item)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $item->medicine->displayName() }}</td>
    <td class="py-2 pr-3">{{ number_format($item->system_quantity,2) }}</td>
    <td class="py-2 pr-3">{{ number_format($item->counted_quantity,2) }}</td>
    <td class="py-2 pr-3 {{ $item->discrepancy != 0 ? 'text-amber-600 font-medium' : '' }}">{{ number_format($item->discrepancy,2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</x-card>
@endsection
