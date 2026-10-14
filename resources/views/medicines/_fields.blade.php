@php $m = $medicine ?? null; @endphp
<div><label class="block text-xs text-slate-500 mb-1">Name *</label><input name="name" value="{{ old('name', $m->name ?? '') }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Generic Name</label><input name="generic_name" value="{{ old('generic_name', $m->generic_name ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Strength</label><input name="strength" value="{{ old('strength', $m->strength ?? '') }}" placeholder="e.g. 500mg" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>

<div><label class="block text-xs text-slate-500 mb-1">Dosage Form *</label>
<select name="dosage_form" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
@foreach(['tablet','capsule','syrup','drop','gel','cream','liquid','injection','supply','other'] as $f)
<option value="{{ $f }}" @selected(old('dosage_form',$m->dosage_form ?? '')===$f)>{{ ucfirst($f) }}</option>
@endforeach
</select></div>

<div><label class="block text-xs text-slate-500 mb-1">Category</label>
<select name="medicine_category_id" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">—</option>
@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('medicine_category_id',$m->medicine_category_id ?? '')==$c->id)>{{ $c->name }}</option>@endforeach
</select></div>

<div><label class="block text-xs text-slate-500 mb-1">Manufacturer</label><input name="manufacturer" value="{{ old('manufacturer', $m->manufacturer ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>

<div><label class="block text-xs text-slate-500 mb-1">Base Unit *</label><input name="base_unit" value="{{ old('base_unit', $m->base_unit ?? '') }}" required placeholder="tablet, ml, piece..." class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Purchase Unit</label><input name="purchase_unit" value="{{ old('purchase_unit', $m->purchase_unit ?? '') }}" placeholder="box, strip, bottle..." class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Issue Unit</label><input name="issue_unit" value="{{ old('issue_unit', $m->issue_unit ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>

<div><label class="block text-xs text-slate-500 mb-1">Units per Purchase Pack *</label><input type="number" step="0.0001" name="units_per_purchase_pack" value="{{ old('units_per_purchase_pack', $m->units_per_purchase_pack ?? 1) }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Preferred Supplier</label>
<select name="preferred_supplier_id" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="">—</option>
@foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('preferred_supplier_id',$m->preferred_supplier_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach
</select></div>
<div><label class="block text-xs text-slate-500 mb-1">Default Purchase Price ({{ config('erp.currency_symbol') }})</label><input type="number" step="0.01" name="default_purchase_price" value="{{ old('default_purchase_price', $m->default_purchase_price ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>

<div><label class="block text-xs text-slate-500 mb-1">Minimum Stock Level *</label><input type="number" step="0.0001" name="minimum_stock_level" value="{{ old('minimum_stock_level', $m->minimum_stock_level ?? 0) }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Reorder Level *</label><input type="number" step="0.0001" name="reorder_level" value="{{ old('reorder_level', $m->reorder_level ?? 0) }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Storage Location</label><input name="storage_location" value="{{ old('storage_location', $m->storage_location ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>

<div class="flex items-center gap-2 pt-6"><input type="checkbox" name="prescription_required" value="1" @checked(old('prescription_required',$m->prescription_required ?? false)) class="rounded border-slate-300"><label class="text-sm text-slate-600">Prescription required</label></div>
<div><label class="block text-xs text-slate-500 mb-1">Status *</label>
<select name="status" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="active" @selected(old('status',$m->status ?? 'active')==='active')>Active</option>
<option value="inactive" @selected(old('status',$m->status ?? '')==='inactive')>Inactive</option>
</select></div>
<div></div>
<div class="md:col-span-3"><label class="block text-xs text-slate-500 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">{{ old('remarks', $m->remarks ?? '') }}</textarea></div>
