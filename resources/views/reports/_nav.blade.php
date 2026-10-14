@php
$reportNav = [
    'reports.purchase-expense' => 'Purchase Expense',
    'reports.employee-expense' => 'Employee Expense',
    'reports.department-expense' => 'Department Expense',
    'reports.medicine-consumption' => 'Medicine Consumption',
    'reports.daily-usage' => 'Daily Usage',
    'reports.monthly-usage' => 'Monthly Usage',
    'reports.highest-cost-medicines' => 'Highest-Cost Medicines',
    'reports.supplier-performance' => 'Supplier Performance',
    'reports.inventory-valuation' => 'Inventory Valuation',
];
@endphp
<div class="flex flex-wrap gap-2 mb-6">
@foreach($reportNav as $route => $label)
<a href="{{ route($route) }}" class="text-xs px-3 py-1.5 rounded-full border {{ request()->routeIs($route) ? 'bg-navy-900 text-white border-navy-900' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
@endforeach
</div>
