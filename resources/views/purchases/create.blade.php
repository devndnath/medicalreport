@extends('layouts.app')
@section('page-title', 'New Stock Receive')
@section('content')
<x-card>
<form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
@csrf
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div><label class="block text-xs text-slate-500 mb-1">Receive Date *</label><input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Received From *</label>
        <select name="source_type" id="sourceType" required onchange="toggleSource()" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="supplier" {{ old('source_type', 'supplier') === 'supplier' ? 'selected' : '' }}>Supplier</option>
            <option value="store" {{ old('source_type') === 'store' ? 'selected' : '' }}>Store</option>
            <option value="accounts" {{ old('source_type') === 'accounts' ? 'selected' : '' }}>Accounts</option>
        </select>
    </div>
    <div id="supplierField"><label class="block text-xs text-slate-500 mb-1">Supplier *</label>
        <select name="supplier_id" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2">
            <option value="">Select supplier</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <div id="receivedFromField" class="hidden"><label class="block text-xs text-slate-500 mb-1">Released By (name/note)</label><input name="received_from" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2" placeholder="e.g. Store in-charge name"></div>
    <div id="invoiceField"><label class="block text-xs text-slate-500 mb-1">Supplier Invoice #</label><input name="supplier_invoice_number" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Payment Amount Now</label><input type="number" step="0.01" name="payment_amount" value="0" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
</div>
<script>
function toggleSource() {
    const type = document.getElementById('sourceType').value;
    document.getElementById('supplierField').classList.toggle('hidden', type !== 'supplier');
    document.getElementById('invoiceField').classList.toggle('hidden', type !== 'supplier');
    document.getElementById('receivedFromField').classList.toggle('hidden', type === 'supplier');
    document.querySelector('#supplierField select').required = (type === 'supplier');
}
toggleSource();
</script>

<h4 class="text-sm font-semibold text-slate-700 mb-2">Line Items</h4>
<div id="lines" class="space-y-3"></div>
<button type="button" onclick="addLine()" class="mt-2 text-sm text-navy-700 hover:underline">+ Add line item</button>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <div><label class="block text-xs text-slate-500 mb-1">Discount (total)</label><input type="number" step="0.01" name="discount_amount" value="0" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Tax/VAT (total)</label><input type="number" step="0.01" name="tax_amount" value="0" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Other Charges</label><input type="number" step="0.01" name="other_charges" value="0" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
</div>
<div class="mt-4"><label class="block text-xs text-slate-500 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></textarea></div>

<div class="mt-6"><button class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-5 py-2.5 rounded-lg">Save Draft Purchase</button></div>
</form>
</x-card>

<script>
const medicines = {!! json_encode($medicines->map(fn($m)=>['id'=>$m->id,'name'=>$m->displayName(),'purchase_unit'=>$m->purchase_unit])) !!};
let lineCount = 0;

function addLine() {
    const idx = lineCount++;
    const options = medicines.map(m => `<option value="${m.id}" data-unit="${m.purchase_unit ?? ''}">${m.name}</option>`).join('');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-8 gap-2 items-end bg-slate-50 p-3 rounded-lg';
    row.innerHTML = `
        <div class="col-span-2"><label class="block text-[11px] text-slate-500">Medicine</label>
            <select name="items[${idx}][medicine_id]" required class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5">${options}</select></div>
        <div><label class="block text-[11px] text-slate-500">Batch #</label><input name="items[${idx}][batch_number]" required class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">Expiry</label><input type="date" name="items[${idx}][expiry_date]" class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">Qty</label><input type="number" step="0.0001" name="items[${idx}][purchase_quantity]" required class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">Free Qty</label><input type="number" step="0.0001" value="0" name="items[${idx}][free_quantity]" class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">Unit Price</label><input type="number" step="0.0001" name="items[${idx}][unit_purchase_price]" required class="w-full rounded-lg border border-slate-300 text-xs px-2 py-1.5"></div>
        <div><button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-600 text-xs">Remove</button></div>
    `;
    document.getElementById('lines').appendChild(row);
}
addLine();
</script>
@endsection
