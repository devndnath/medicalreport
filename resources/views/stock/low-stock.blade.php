@extends('layouts.app')
@section('page-title', 'Low Stock Report')
@section('content')
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Current Stock</th><th class="py-2 pr-3">Reorder Level</th><th class="py-2 pr-3">Shortfall</th></tr></thead>
<tbody>
@foreach($medicines as $m)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3"><a href="{{ route('medicines.show',$m) }}" class="text-navy-900 hover:underline">{{ $m->displayName() }}</a></td>
    <td class="py-2 pr-3 text-amber-600 font-medium">{{ number_format($m->currentStock(),2) }}</td>
    <td class="py-2 pr-3">{{ number_format($m->reorder_level,2) }}</td>
    <td class="py-2 pr-3">{{ number_format(max(0,$m->reorder_level-$m->currentStock()),2) }}</td>
</tr>
@endforeach
@if($medicines->isEmpty())<tr><td colspan="4" class="py-6 text-center text-slate-400">No low-stock items. 🎉</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
