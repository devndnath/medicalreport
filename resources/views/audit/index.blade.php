@extends('layouts.app')
@section('page-title', 'Audit Logs')
@section('content')
<form method="GET" class="flex gap-2 mb-4">
    <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action..." class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
    <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
</form>
<x-card>
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">Time</th><th class="py-2 pr-3">User</th><th class="py-2 pr-3">Action</th><th class="py-2 pr-3">Entity</th><th class="py-2 pr-3">IP</th></tr></thead>
<tbody>
@foreach($logs as $log)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
    <td class="py-2 pr-3">{{ $log->user->name ?? 'System' }}</td>
    <td class="py-2 pr-3">{{ $log->action }}</td>
    <td class="py-2 pr-3 text-xs text-slate-500">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '' }}</td>
    <td class="py-2 pr-3">{{ $log->ip_address }}</td>
</tr>
@endforeach
@if($logs->isEmpty())<tr><td colspan="5" class="py-6 text-center text-slate-400">No audit entries yet.</td></tr>@endif
</tbody>
</table>
<div class="mt-4">{{ $logs->links() }}</div>
</x-card>
@endsection
