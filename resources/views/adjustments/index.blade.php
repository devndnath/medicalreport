@extends('layouts.app')
@section('page-title', 'Stock Adjustments')
@section('content')
<div class="flex justify-end mb-4"><a href="{{ route('adjustments.create') }}" class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">+ New Adjustment</a></div>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Adj #</th><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Direction</th><th class="py-2 pr-3">Reason</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Approved By</th></tr></thead>
<tbody>
@foreach($adjustments as $a)
<tr class="border-b border-slate-50"><td class="py-2 pr-3">{{ $a->adjustment_number }}</td><td class="py-2 pr-3">{{ $a->adjustment_date->format('d-m-Y') }}</td><td class="py-2 pr-3">{{ $a->medicine->displayName() }}</td><td class="py-2 pr-3 capitalize">{{ $a->direction }}</td><td class="py-2 pr-3 capitalize">{{ str_replace('_',' ',$a->reason) }}</td><td class="py-2 pr-3">{{ number_format($a->quantity,2) }}</td><td class="py-2 pr-3">{{ $a->approvedBy->name ?? '—' }}</td></tr>
@endforeach
@if($adjustments->isEmpty())<tr><td colspan="7" class="py-6 text-center text-slate-400">No adjustments recorded yet.</td></tr>@endif
</tbody>
</table>
<div class="mt-4">{{ $adjustments->links() }}</div>
</x-card>
@endsection
