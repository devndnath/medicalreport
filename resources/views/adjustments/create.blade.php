@extends('layouts.app')
@section('page-title', 'New Stock Adjustment')
@section('content')
<x-card>
<form method="POST" action="{{ route('adjustments.store') }}" class="grid grid-cols-2 gap-4">
@csrf
<div class="col-span-2"><label class="block text-xs text-slate-500 mb-1">Medicine *</label>
<select name="medicine_id" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">Select medicine</option>
@foreach($medicines as $m)<option value="{{ $m->id }}">{{ $m->displayName() }} — stock {{ number_format($m->currentStock(),2) }}</option>@endforeach
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Direction *</label>
<select name="direction" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"><option value="in">In</option><option value="out">Out</option></select></div>
<div><label class="block text-xs text-slate-500 mb-1">Reason *</label>
<select name="reason" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="damaged">Damaged</option><option value="expired">Expired</option><option value="physical_count_correction">Physical Count Correction</option><option value="data_correction">Data Correction</option><option value="other">Other</option>
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Quantity *</label><input type="number" step="0.0001" name="quantity" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div class="col-span-2"><label class="block text-xs text-slate-500 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></textarea></div>
<div class="col-span-2"><button class="bg-accent-500 text-white text-sm px-5 py-2.5 rounded-lg">Post Adjustment</button></div>
</form>
</x-card>
@endsection
