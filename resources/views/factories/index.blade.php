@extends('layouts.app')
@section('page-title', 'Factories')
@section('content')
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-slate-500">Each factory has its own medicines, employees, departments and stock. Use the factory switcher (top right) to view one at a time.</p>
</div>

<x-card title="Add Factory" class="mb-6">
    <form method="POST" action="{{ route('factories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @csrf
        <div><label class="block text-xs text-slate-500 mb-1">Name *</label><input name="name" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
        <div><label class="block text-xs text-slate-500 mb-1">Short Code *</label><input name="code" required placeholder="e.g. F1" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
        <div class="md:col-span-2"><label class="block text-xs text-slate-500 mb-1">Address</label><input name="address" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
        <div><button class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-5 py-2.5 rounded-lg">Add Factory</button></div>
    </form>
</x-card>

<x-card title="All Factories">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Code</th><th class="py-2 pr-4">Employees</th><th class="py-2 pr-4">Medicines</th><th class="py-2 pr-4">Users</th><th class="py-2 pr-4">Status</th><th></th></tr></thead>
        <tbody>
        @foreach($factories as $f)
        <tr class="border-b border-slate-50">
            <td class="py-2 pr-4 font-medium">{{ $f->name }}</td>
            <td class="py-2 pr-4">{{ $f->code }}</td>
            <td class="py-2 pr-4">{{ $f->employees_count }}</td>
            <td class="py-2 pr-4">{{ $f->medicines_count }}</td>
            <td class="py-2 pr-4">{{ $f->users_count }}</td>
            <td class="py-2 pr-4"><span class="px-2 py-0.5 rounded-full text-xs {{ $f->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td class="py-2 pr-4 text-right">
                <form method="POST" action="{{ route('factories.update', $f) }}" class="inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $f->name }}">
                    <input type="hidden" name="code" value="{{ $f->code }}">
                    <input type="hidden" name="is_active" value="{{ $f->is_active ? '0' : '1' }}">
                    <button class="text-navy-700 text-xs hover:underline">{{ $f->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</x-card>
@endsection
