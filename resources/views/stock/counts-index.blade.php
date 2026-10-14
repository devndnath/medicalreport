@extends('layouts.app')
@section('page-title', 'Physical Stock Counts')
@section('content')
<div class="flex justify-end mb-4"><a href="{{ route('stock.counts.create') }}" class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">+ New Count</a></div>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Count #</th><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Items</th><th class="py-2 pr-3">Status</th></tr></thead>
<tbody>
@foreach($counts as $c)
<tr class="border-b border-slate-50"><td class="py-2 pr-3"><a href="{{ route('stock.counts.show',$c) }}" class="text-navy-900 hover:underline">{{ $c->count_number }}</a></td><td class="py-2 pr-3">{{ $c->count_date->format('d-m-Y') }}</td><td class="py-2 pr-3">{{ $c->items_count }}</td><td class="py-2 pr-3 capitalize">{{ $c->status }}</td></tr>
@endforeach
@if($counts->isEmpty())<tr><td colspan="4" class="py-6 text-center text-slate-400">No physical counts recorded yet.</td></tr>@endif
</tbody>
</table>
<div class="mt-4">{{ $counts->links() }}</div>
</x-card>
@endsection
