@extends('layouts.app')
@section('page-title', 'Medicine Categories')
@section('content')
<x-card title="Add Category" class="mb-6">
<form method="POST" action="{{ route('medicine-categories.store') }}" class="flex gap-3">
    @csrf
    <input name="name" placeholder="Category name" required class="rounded-lg border border-slate-300 text-sm px-3 py-2 flex-1">
    <button class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">Add</button>
</form>
</x-card>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Name</th><th class="py-2">Medicines</th><th></th></tr></thead>
        <tbody>
        @foreach($categories as $c)
            <tr class="border-b border-slate-50"><td class="py-2">{{ $c->name }}</td><td class="py-2">{{ $c->medicines()->count() }}</td>
            <td class="py-2 text-right">
                <form method="POST" action="{{ route('medicine-categories.destroy',$c) }}" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="text-rose-600 text-xs hover:underline">Delete</button></form>
            </td></tr>
        @endforeach
        </tbody>
    </table>
</x-card>
@endsection
