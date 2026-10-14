@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Executive Dashboard')

@section('content')
@php $currency = config('erp.currency_symbol'); @endphp

<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Period</label>
        <select name="range" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-3 py-2">
            @foreach(['today'=>'Today','this_week'=>'This Week','this_month'=>'This Month','this_quarter'=>'This Quarter','this_year'=>'This Year','custom'=>'Custom Range'] as $k=>$v)
                <option value="{{ $k }}" @selected($label===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    @if($label === 'custom')
        <div><label class="block text-xs text-slate-500 mb-1">From</label><input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
        <div><label class="block text-xs text-slate-500 mb-1">To</label><input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 text-sm px-3 py-2"></div>
        <button class="bg-navy-900 text-white text-sm px-4 py-2 rounded-lg">Apply</button>
    @endif
</form>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
    <x-kpi-card label="Purchase Cost" value="{{ $currency }}{{ number_format($kpi['purchase_cost'],2) }}" />
    <x-kpi-card label="Issue Cost" value="{{ $currency }}{{ number_format($kpi['issue_cost'],2) }}" />
    <x-kpi-card label="Inventory Value" value="{{ $currency }}{{ number_format($kpi['inventory_value'],2) }}" />
    <x-kpi-card label="Medicines & Supplies" value="{{ $kpi['total_medicines'] }}" />
    <x-kpi-card label="Units Received" value="{{ number_format($kpi['units_received'],1) }}" />
    <x-kpi-card label="Units Issued" value="{{ number_format($kpi['units_issued'],1) }}" />
    <x-kpi-card label="Low Stock Items" value="{{ $kpi['low_stock_items'] }}" tone="{{ $kpi['low_stock_items']>0?'warn':'good' }}" />
    <x-kpi-card label="Expiring Soon (90d)" value="{{ $kpi['expiring_soon'] }}" tone="{{ $kpi['expiring_soon']>0?'warn':'good' }}" />
    <x-kpi-card label="Expired Items" value="{{ $kpi['expired_items'] }}" tone="{{ $kpi['expired_items']>0?'danger':'good' }}" />
    <x-kpi-card label="Employees Served" value="{{ $kpi['employees_served'] }}" />
    <x-kpi-card label="Monthly Medical Expense" value="{{ $currency }}{{ number_format($kpi['monthly_expense'],2) }}" />
    <x-kpi-card label="Pending Purchase Bills" value="{{ $currency }}{{ number_format($kpi['pending_purchase_bills'],2) }}" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-card title="Purchase Cost vs. Issue Cost (last 12 months)">
        <canvas id="purchaseVsIssue" height="220"></canvas>
    </x-card>
    <x-card title="Monthly Consumption Trend">
        <canvas id="consumptionTrend" height="220"></canvas>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <x-card title="Top 10 Most Issued Medicines">
        <canvas id="topMedicines" height="240"></canvas>
    </x-card>
    <x-card title="Top 10 Employees by Medicine Expense">
        <canvas id="topEmployees" height="240"></canvas>
    </x-card>
    <x-card title="Stock Value by Category">
        <canvas id="categoryValue" height="240"></canvas>
    </x-card>
</div>

<x-card title="Department-wise Medical Expense">
    <canvas id="deptExpense" height="160"></canvas>
</x-card>

<x-card title="Recent Activity" class="mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b border-slate-100">
                <th class="py-2 pr-4">Date</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Medicine</th><th class="py-2 pr-4">In</th><th class="py-2 pr-4">Out</th><th class="py-2 pr-4">Balance</th>
            </tr></thead>
            <tbody>
                @foreach($recentActivity as $t)
                <tr class="border-b border-slate-50">
                    <td class="py-2 pr-4">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4 capitalize">{{ str_replace('_',' ',$t->transaction_type) }}</td>
                    <td class="py-2 pr-4">{{ $t->medicine_name }}</td>
                    <td class="py-2 pr-4">{{ $t->quantity_in > 0 ? number_format($t->quantity_in,2) : '—' }}</td>
                    <td class="py-2 pr-4">{{ $t->quantity_out > 0 ? number_format($t->quantity_out,2) : '—' }}</td>
                    <td class="py-2 pr-4">{{ number_format($t->running_balance,2) }}</td>
                </tr>
                @endforeach
                @if($recentActivity->isEmpty())
                <tr><td colspan="6" class="py-6 text-center text-slate-400">No stock activity yet.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</x-card>

<script>
const palette = ['#0e7c66','#16233a','#2c4567','#b45309','#9f1239','#334155'];

new Chart(document.getElementById('purchaseVsIssue'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyPurchase->keys()->merge($monthlyIssue->keys())->unique()->sort()->values()) !!},
        datasets: [
            {label: 'Purchase Cost', data: {!! json_encode(array_values($monthlyPurchase->toArray())) !!}, backgroundColor: palette[0]},
            {label: 'Issue Cost', data: {!! json_encode(array_values($monthlyIssue->toArray())) !!}, backgroundColor: palette[1]},
        ]
    },
    options: {responsive:true, plugins:{legend:{position:'bottom'}}}
});

new Chart(document.getElementById('consumptionTrend'), {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyIssue->keys()->values()) !!},
        datasets: [{label:'Issue Cost Trend', data: {!! json_encode(array_values($monthlyIssue->toArray())) !!}, borderColor: palette[0], tension:.3, fill:false}]
    },
    options: {responsive:true, plugins:{legend:{display:false}}}
});

new Chart(document.getElementById('topMedicines'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topMedicines->map(fn($r)=>$r->medicine?->displayName() ?? 'Unknown')) !!},
        datasets: [{label:'Qty Issued', data: {!! json_encode($topMedicines->pluck('qty')) !!}, backgroundColor: palette[2]}]
    },
    options: {indexAxis:'y', plugins:{legend:{display:false}}}
});

new Chart(document.getElementById('topEmployees'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topEmployees->map(fn($r)=>$r->employee?->name ?? 'Unknown')) !!},
        datasets: [{label:'Expense', data: {!! json_encode($topEmployees->pluck('cost')) !!}, backgroundColor: palette[3]}]
    },
    options: {indexAxis:'y', plugins:{legend:{display:false}}}
});

new Chart(document.getElementById('categoryValue'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($categoryValue->keys()) !!},
        datasets: [{data: {!! json_encode($categoryValue->values()) !!}, backgroundColor: palette}]
    },
    options: {plugins:{legend:{position:'bottom'}}}
});

new Chart(document.getElementById('deptExpense'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($departmentExpense->map(fn($r)=>$r->department?->name ?? 'Unassigned')) !!},
        datasets: [{label:'Expense', data: {!! json_encode($departmentExpense->pluck('cost')) !!}, backgroundColor: palette[4]}]
    },
    options: {plugins:{legend:{display:false}}}
});
</script>
@endsection
