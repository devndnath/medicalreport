@extends('layouts.app')
@section('page-title', 'Departments')
@section('content')
<x-card title="Add Department" class="mb-6">
<form method="POST" action="{{ route('departments.store') }}" class="flex gap-3">
    @csrf
    <input name="name" placeholder="Department name" required class="rounded-lg border border-slate-300 text-sm px-3 py-2 flex-1">
    <input name="legacy_label" placeholder="Original label (optional)" class="rounded-lg border border-slate-300 text-sm px-3 py-2 flex-1">
    <button class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">Add</button>
</form>
</x-card>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2">Name</th><th class="py-2">Legacy label</th><th class="py-2">Employees</th><th></th></tr></thead>
        <tbody>
        @foreach($departments as $d)
            <tr class="border-b border-slate-50">
                <td class="py-2">{{ $d->name }}</td><td class="py-2 text-slate-500">{{ $d->legacy_label }}</td><td class="py-2">{{ $d->employees_count }}</td>
                <td class="py-2 text-right">
                    <form method="POST" action="{{ route('departments.destroy',$d) }}" class="inline" onsubmit="return confirm('Delete this department?')">@csrf @method('DELETE')<button class="text-rose-600 text-xs hover:underline">Delete</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $departments->links() }}</div>
</x-card>
@endsection
