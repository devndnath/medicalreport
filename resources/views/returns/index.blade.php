@extends('layouts.app')
@section('page-title', 'Returns')
@section('content')
<div class="flex justify-end mb-4"><a href="{{ route('returns.create') }}" class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">+ New Return</a></div>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Return #</th><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Medicine</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Condition</th></tr></thead>
<tbody>
@foreach($returns as $r)
<tr class="border-b border-slate-50"><td class="py-2 pr-3">{{ $r->return_number }}</td><td class="py-2 pr-3">{{ $r->return_date->format('d-m-Y') }}</td><td class="py-2 pr-3">{{ $r->medicine->displayName() }}</td><td class="py-2 pr-3">{{ number_format($r->quantity,2) }}</td><td class="py-2 pr-3 capitalize">{{ $r->condition }}</td></tr>
@endforeach
@if($returns->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">No returns recorded yet.</td></tr>@endif
</tbody>
</table>
<div class="mt-4">{{ $returns->links() }}</div>
</x-card>
@endsection
