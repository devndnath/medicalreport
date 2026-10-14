@php $s = $supplier ?? null; @endphp
<div><label class="block text-xs text-slate-500 mb-1">Name *</label><input name="name" value="{{ old('name', $s->name ?? '') }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Contact Person</label><input name="contact_person" value="{{ old('contact_person', $s->contact_person ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Phone</label><input name="phone" value="{{ old('phone', $s->phone ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Email</label><input name="email" value="{{ old('email', $s->email ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Business Details</label><input name="business_details" value="{{ old('business_details', $s->business_details ?? '') }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Status *</label>
<select name="status" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
<option value="active" @selected(old('status',$s->status ?? 'active')==='active')>Active</option>
<option value="inactive" @selected(old('status',$s->status ?? '')==='inactive')>Inactive</option>
</select></div>
<div class="md:col-span-2"><label class="block text-xs text-slate-500 mb-1">Address</label><textarea name="address" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">{{ old('address', $s->address ?? '') }}</textarea></div>
<div><label class="block text-xs text-slate-500 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">{{ old('remarks', $s->remarks ?? '') }}</textarea></div>
