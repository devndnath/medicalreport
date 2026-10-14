@extends('layouts.app')
@section('page-title', 'Medicine Master')
@section('content')
<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU..." class="rounded-lg border border-slate-300 text-sm px-3 py-2 w-64">
        <select name="category_id" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="">All categories</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('medicines.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-4 py-2 rounded-lg">+ New Medicine</a>
</div>

<x-card>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b border-slate-100">
            <th class="py-2 pr-4">SKU</th><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Form</th><th class="py-2 pr-4">Category</th>
            <th class="py-2 pr-4">Stock</th><th class="py-2 pr-4">Reorder Lvl</th><th class="py-2 pr-4">Status</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($medicines as $m)
            <tr class="border-b border-slate-50 hover:bg-slate-50">
                <td class="py-2 pr-4 font-mono text-xs">{{ $m->sku }}</td>
                <td class="py-2 pr-4"><a href="{{ route('medicines.show',$m) }}" class="font-medium text-navy-900 hover:underline">{{ $m->displayName() }}</a></td>
                <td class="py-2 pr-4 capitalize">{{ $m->dosage_form }}</td>
                <td class="py-2 pr-4">{{ $m->category->name ?? '—' }}</td>
                <td class="py-2 pr-4 {{ $m->isLowStock() ? 'text-amber-600 font-medium' : '' }}">{{ number_format($m->currentStock(),2) }} {{ $m->base_unit }}</td>
                <td class="py-2 pr-4">{{ number_format($m->reorder_level,2) }}</td>
                <td class="py-2 pr-4"><span class="px-2 py-0.5 rounded-full text-xs {{ $m->status==='active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $m->status }}</span></td>
                <td class="py-2 pr-4 text-right"><a href="{{ route('medicines.edit',$m) }}" class="text-xs text-navy-700 hover:underline">Edit</a></td>
            </tr>
        @endforeach
        @if($medicines->isEmpty())<tr><td colspan="8" class="py-6 text-center text-slate-400">No medicines found.</td></tr>@endif
        </tbody>
    </table>
    </div>
    <div class="mt-4">{{ $medicines->links() }}</div>
</x-card>
@endsection
