@extends('layouts.app')
@section('page-title', 'New User')
@section('content')
<x-card title="Create User">
<form method="POST" action="{{ route('users.store') }}" class="grid grid-cols-2 gap-4">
@csrf
<div><label class="block text-xs text-slate-500 mb-1">Full Name *</label><input name="name" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Email *</label><input type="email" name="email" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Password *</label><input type="password" name="password" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Role *</label>
<select name="role_id" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
</select></div>
@if(!empty($factories))
<div><label class="block text-xs text-slate-500 mb-1">Factory (leave blank for Super Admin)</label>
<select name="factory_id" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">—</option>
@foreach($factories as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach
</select></div>
@endif
<div class="col-span-2"><button class="bg-accent-500 text-white text-sm px-5 py-2.5 rounded-lg">Create User</button></div>
</form>
</x-card>
<p class="mt-4 text-sm"><a href="{{ route('users.index') }}" class="text-navy-700 hover:underline">← Back to user list (you can also add users there directly)</a></p>
@endsection
