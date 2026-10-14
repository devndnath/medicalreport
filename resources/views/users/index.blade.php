@extends('layouts.app')
@section('page-title', 'User Management')
@section('content')
<x-card title="Add User" class="mb-6">
<form method="POST" action="{{ route('users.store') }}" class="grid grid-cols-4 gap-3">
@csrf
<input name="name" placeholder="Full name" required class="rounded-lg border border-slate-300 text-sm px-3 py-2">
<input type="email" name="email" placeholder="Email" required class="rounded-lg border border-slate-300 text-sm px-3 py-2">
<input type="password" name="password" placeholder="Password" required class="rounded-lg border border-slate-300 text-sm px-3 py-2">
<select name="role_id" required class="rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">Role</option>
@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
</select>
<div class="col-span-4"><button class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">Create User</button></div>
</form>
</x-card>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Name</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Role</th><th class="py-2 pr-3">Status</th><th></th></tr></thead>
<tbody>
@foreach($users as $u)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $u->name }}</td>
    <td class="py-2 pr-3">{{ $u->email }}</td>
    <td class="py-2 pr-3">
        <form method="POST" action="{{ route('users.update',$u) }}" class="inline-flex items-center gap-2">
        @csrf @method('PUT')
        <input type="hidden" name="name" value="{{ $u->name }}"><input type="hidden" name="email" value="{{ $u->email }}">
        <select name="role_id" onchange="this.form.submit()" class="rounded border border-slate-300 text-xs px-2 py-1">
        @foreach($roles as $r)<option value="{{ $r->id }}" @selected($u->role_id==$r->id)>{{ $r->name }}</option>@endforeach
        </select>
        <input type="hidden" name="is_active" value="{{ $u->is_active ? 1 : 0 }}">
        </form>
    </td>
    <td class="py-2 pr-3">{{ $u->is_active ? 'Active' : 'Disabled' }}</td>
    <td class="py-2 pr-3 text-right">
        @if($u->id !== auth()->id())
        <form method="POST" action="{{ route('users.destroy',$u) }}" class="inline" onsubmit="return confirm('Remove this user?')">@csrf @method('DELETE')<button class="text-rose-600 text-xs hover:underline">Remove</button></form>
        @endif
    </td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $users->links() }}</div>
</x-card>
@endsection
