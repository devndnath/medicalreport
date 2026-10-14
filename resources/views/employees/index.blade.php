@extends('layouts.app')
@section('page-title', 'Employee Directory')
@section('content')
<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or ID..." class="rounded-lg border border-slate-300 text-sm px-3 py-2 w-64">
        <select name="department_id" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="">All departments</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach
        </select>
        <label class="flex items-center gap-1 text-sm text-slate-600"><input type="checkbox" name="needs_id" value="1" @checked(request('needs_id'))> Needs ID confirmation</label>
        <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('employees.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-4 py-2 rounded-lg">+ New Employee</a>
</div>
<x-card>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-4">ID</th><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Department</th><th class="py-2 pr-4">Status</th><th></th></tr></thead>
        <tbody>
        @foreach($employees as $e)
        <tr class="border-b border-slate-50 hover:bg-slate-50">
            <td class="py-2 pr-4 font-mono text-xs">{{ $e->employee_code }} @if($e->needs_id_confirmation)<span class="ml-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px]">needs ID</span>@endif</td>
            <td class="py-2 pr-4"><a href="{{ route('employees.show',$e) }}" class="font-medium text-navy-900 hover:underline">{{ $e->name }}</a></td>
            <td class="py-2 pr-4">{{ $e->department->name ?? '—' }}</td>
            <td class="py-2 pr-4 capitalize">{{ $e->employment_status }}</td>
            <td class="py-2 pr-4 text-right"><a href="{{ route('employees.edit',$e) }}" class="text-xs text-navy-700 hover:underline">Edit</a></td>
        </tr>
        @endforeach
        @if($employees->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">No employees found.</td></tr>@endif
        </tbody>
    </table>
    <div class="mt-4">{{ $employees->links() }}</div>
</x-card>
@endsection
