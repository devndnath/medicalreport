@extends('layouts.app')
@section('page-title', 'Batches — '.$medicine->displayName())
@section('content')
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Batch</th><th class="py-2 pr-3">Received</th><th class="py-2 pr-3">Expiry</th><th class="py-2 pr-3">Received Qty</th><th class="py-2 pr-3">Remaining</th><th class="py-2 pr-3">Unit Cost</th><th class="py-2 pr-3">Status</th></tr></thead>
<tbody>
@foreach($batches as $b)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $b->batch_number }}</td>
    <td class="py-2 pr-3">{{ $b->received_date->format('d-m-Y') }}</td>
    <td class="py-2 pr-3">{{ $b->expiry_date?->format('d-m-Y') ?? '—' }}</td>
    <td class="py-2 pr-3">{{ number_format($b->received_quantity,2) }}</td>
    <td class="py-2 pr-3">{{ number_format($b->remaining_quantity,2) }}</td>
    <td class="py-2 pr-3">{{ $b->cost_unavailable ? 'N/A' : number_format($b->unit_cost,4) }}</td>
    <td class="py-2 pr-3 capitalize">{{ $b->status }}</td>
</tr>
@endforeach
</tbody>
</table>
</x-card>
@endsection
