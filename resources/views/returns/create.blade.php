@extends('layouts.app')
@section('page-title', 'New Return')
@section('content')
<x-card>
<form method="POST" action="{{ route('returns.store') }}" class="grid grid-cols-2 gap-4">
@csrf
<div class="col-span-2"><label class="block text-xs text-slate-500 mb-1">Issue Line *</label>
<select name="medicine_issue_item_id" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">Select the original issue line</option>
@foreach($issueItems as $i)
<option value="{{ $i->id }}">{{ $i->voucher->voucher_number }} — {{ $i->voucher->employee->name }} — {{ $i->medicine->displayName() }} ({{ number_format($i->quantity_issued,2) }} {{ $i->issue_unit }})</option>
@endforeach
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Quantity *</label><input type="number" step="0.0001" name="quantity" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Condition *</label>
<select name="condition" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="usable">Usable (returns to stock)</option>
<option value="damaged">Damaged (written off)</option>
</select></div>
<div class="col-span-2"><label class="block text-xs text-slate-500 mb-1">Reason</label><textarea name="reason" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></textarea></div>
<div class="col-span-2"><button class="bg-accent-500 text-white text-sm px-5 py-2.5 rounded-lg">Record Return</button></div>
</form>
</x-card>
@endsection
