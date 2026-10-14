@extends('layouts.app')
@section('page-title', 'Issue Medicine')
@section('content')
<x-card>
<form method="POST" action="{{ route('issues.store') }}">
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div><label class="block text-xs text-slate-500 mb-1">Employee *</label>
        <select name="employee_id" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="">Select employee</option>
            @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }}) @if($e->department) — {{ $e->department->name }}@endif</option>@endforeach
        </select>
    </div>
    <div><label class="block text-xs text-slate-500 mb-1">Symptoms / Complaint</label><input name="symptoms" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
</div>

<h4 class="text-sm font-semibold text-slate-700 mb-2">Medicines</h4>
<div id="lines" class="space-y-3"></div>
<button type="button" onclick="addLine()" class="mt-2 text-sm text-navy-700 hover:underline">+ Add medicine line</button>

<div class="mt-4"><label class="block text-xs text-slate-500 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></textarea></div>
<div class="mt-6"><button class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-5 py-2.5 rounded-lg">Issue & Deduct Stock</button></div>
</form>
</x-card>

<script>
const medicines = {!! json_encode($medicines) !!};
let lineCount = 0;

function addLine() {
    const idx = lineCount++;
    const options = medicines.map(m => `<option value="${m.id}" data-stock="${m.stock}" data-unit="${m.unit}">${m.name} — stock: ${Number(m.stock).toFixed(2)} ${m.unit}</option>`).join('');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-6 gap-2 items-end bg-slate-50 p-3 rounded-lg';
    row.innerHTML = `
        <div class="col-span-3"><label class="block text-[11px] text-slate-500">Medicine</label>
            <select name="lines[${idx}][medicine_id]" required onchange="updateUnit(this)" class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5">${options}</select></div>
        <div><label class="block text-[11px] text-slate-500">Quantity</label><input type="number" step="0.0001" name="lines[${idx}][quantity_issued]" required class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">Unit</label><input type="text" name="lines[${idx}][issue_unit]" readonly class="unit-field w-full rounded-lg border border-slate-200 bg-slate-100 text-xs px-2 py-1.5"></div>
        <div><button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-600 text-xs">Remove</button></div>
    `;
    document.getElementById('lines').appendChild(row);
    updateUnit(row.querySelector('select'));
}
function updateUnit(select) {
    const unit = select.selectedOptions[0]?.dataset.unit ?? '';
    select.closest('div.grid').querySelector('.unit-field').value = unit;
}
addLine();
</script>
@endsection
