@extends('layouts.app')
@section('page-title', 'Settings')
@section('content')
<x-card title="Business Settings">
<form method="POST" action="{{ route('settings.update') }}" class="grid grid-cols-3 gap-4">
@csrf @method('PUT')
<div><label class="block text-xs text-slate-500 mb-1">Low Stock Threshold (days)</label><input type="number" name="low_stock_default_threshold_days" value="{{ \App\Models\ApplicationSetting::get('low_stock_default_threshold_days', config('erp.low_stock_default_threshold_days')) }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Expiry Alert (days)</label><input type="number" name="expiry_alert_days" value="{{ \App\Models\ApplicationSetting::get('expiry_alert_days', 90) }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div><label class="block text-xs text-slate-500 mb-1">Currency Symbol</label><input name="currency_symbol" value="{{ \App\Models\ApplicationSetting::get('currency_symbol', config('erp.currency_symbol')) }}" class="w-full rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
<div class="col-span-3"><button class="bg-accent-500 text-white text-sm px-5 py-2.5 rounded-lg">Save Settings</button></div>
</form>
</x-card>

<x-card title="Valuation Policy" class="mt-6">
    <p class="text-sm text-slate-600">Inventory valuation policy: <strong>FIFO (First-In-First-Out)</strong>, implemented consistently across purchases, issues, returns, and reports. This is documented in <code>config/erp.php</code> and should not be changed without re-costing existing batches.</p>
</x-card>
@endsection
