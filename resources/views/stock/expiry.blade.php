@extends('layouts.app')
@section('page-title', 'Expiry Management')
@section('content')
<form method="GET" class="mb-4 flex gap-2">
    <select name="days" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
        @foreach([30,60,90] as $d)<option value="{{ $d }}" @selected($days==$d)>Within {{ $d }} days</option>@endforeach
    </select>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Batch</th><th class="py-2 pr-3">Expiry Date</th><th class="py-2 pr-3">Remaining Qty</th><th class="py-2 pr-3">Status</th></tr></thead>
<tbody>
@foreach($batches as $b)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $b->medicine->displayName() }}</td>
    <td class="py-2 pr-3">{{ $b->batch_number }}</td>
    <td class="py-2 pr-3">{{ $b->expiry_date->format('d-m-Y') }}</td>
    <td class="py-2 pr-3">{{ number_format($b->remaining_quantity,2) }}</td>
    <td class="py-2 pr-3">
        @if($b->isExpired())<span class="px-2 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700">Expired</span>
        @else<span class="px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700">Expiring soon</span>@endif
    </td>
</tr>
@endforeach
@if($batches->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">Nothing expiring in this window.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
