<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 900: '#0f1b2d', 800: '#16233a', 700: '#1f3350', 600: '#2c4567' },
                        accent: { 500: '#0e7c66', 600: '#0b6854' }
                    },
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',ui-sans-serif,system-ui;} .scrollbar-thin::-webkit-scrollbar{width:6px} .scrollbar-thin::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 bg-navy-900 text-slate-200 shrink-0">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10">
            <div class="w-8 h-8 rounded-md bg-accent-500 flex items-center justify-center font-bold text-white">+</div>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-white">Medical Store</p>
                <p class="text-[11px] text-slate-400">Factory ERP</p>
            </div>
        </div>
        <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 text-sm">
            @php
                $nav = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '📊'],
                    ['label' => 'Medicine Master', 'route' => 'medicines.index', 'icon' => '💊'],
                    ['label' => 'Employee Directory', 'route' => 'employees.index', 'icon' => '👥'],
                    ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => '🚚'],
                    ['label' => 'Purchases', 'route' => 'purchases.index', 'icon' => '🧾'],
                    ['label' => 'Medicine Issue', 'route' => 'issues.index', 'icon' => '➡️'],
                    ['label' => 'Stock Ledger', 'route' => 'stock.ledger', 'icon' => '📒'],
                    ['label' => 'Returns & Adjustments', 'route' => 'returns.index', 'icon' => '↩️'],
                    ['label' => 'Expiry Management', 'route' => 'stock.expiry', 'icon' => '⏰'],
                    ['label' => 'Reports', 'route' => 'reports.purchase-expense', 'icon' => '📈'],
                    ['label' => 'Import Workbook', 'route' => 'import.create', 'icon' => '📥'],
                    ['label' => 'User Management', 'route' => 'users.index', 'icon' => '🔐'],
                    ['label' => 'Factories', 'route' => 'factories.index', 'icon' => '🏭', 'super_admin_only' => true],
                    ['label' => 'Audit Logs', 'route' => 'audit-logs.index', 'icon' => '🕵️'],
                    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => '⚙️'],
                ];
            @endphp
            @foreach($nav as $item)
                @if(\Illuminate\Support\Facades\Route::has($item['route']) && (empty($item['super_admin_only']) || auth()->user()?->isSuperAdmin()))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-5 py-2.5 mx-2 rounded-lg transition
                       {{ request()->routeIs(explode('.', $item['route'])[0].'.*') || request()->routeIs($item['route']) ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300' }}">
                        <span class="text-base">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="p-4 border-t border-white/10 text-xs text-slate-400">
            v1.0 · FIFO costing · {{ config('erp.currency_code') }}
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="lg:hidden font-semibold text-navy-900">Medical Store ERP</span>
                <h1 class="text-lg font-semibold text-navy-900 hidden lg:block">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('factories.switch') }}" class="flex items-center gap-2">
                            @csrf
                            <label class="text-xs text-slate-500">Viewing factory:</label>
                            <select name="factory_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 text-sm px-2 py-1.5">
                                @foreach(\App\Models\Factory::orderBy('name')->get() as $f)
                                    <option value="{{ $f->id }}" @selected(\App\Support\CurrentFactory::id() === $f->id)>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @elseif(auth()->user()->operatingFactory)
                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ auth()->user()->operatingFactory->name }}</span>
                    @endif
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->role->name ?? 'User' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-600">Log out</button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
