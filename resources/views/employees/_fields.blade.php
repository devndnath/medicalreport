@php $e = $employee ?? null; @endphp
<div><label class="block text-xs text-slate-500 mb-1">Employee ID *</label><input name="employee_code" value="{{ old('employee_code', $e->employee_code ?? '') }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Name *</label><input name="name" value="{{ old('name', $e->name ?? '') }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Department</label>
<select name="department_id" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">—</option>
@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$e->department_id ?? '')==$d->id)>{{ $d->name }}</option>@endforeach
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Designation</label><input name="designation" value="{{ old('designation', $e->designation ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Employment Status *</label>
<select name="employment_status" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
@foreach(['active','inactive','resigned','terminated'] as $s)<option value="{{ $s }}" @selected(old('employment_status',$e->employment_status ?? 'active')===$s)>{{ ucfirst($s) }}</option>@endforeach
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Phone</label><input name="phone" value="{{ old('phone', $e->phone ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Joining Date</label><input type="date" name="joining_date" value="{{ old('joining_date', $e->joining_date?->format('Y-m-d') ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div class="md:col-span-2"><label class="block text-xs text-slate-500 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">{{ old('remarks', $e->remarks ?? '') }}</textarea></div>
