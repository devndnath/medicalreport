<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',ui-sans-serif,system-ui;}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-[#0e7c66] text-white flex items-center justify-center font-bold text-xl mx-auto mb-3">+</div>
            <h1 class="text-xl font-semibold text-[#0f1b2d]">Factory Medicine Store</h1>
            <p class="text-sm text-slate-500">Inventory & Medical Expense Management</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0e7c66]/40 focus:border-[#0e7c66]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0e7c66]/40 focus:border-[#0e7c66]">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300">
                    Remember me
                </label>
                <button type="submit" class="w-full bg-[#0f1b2d] hover:bg-[#16233a] text-white rounded-lg py-2.5 text-sm font-medium transition">
                    Sign in
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-slate-400 mt-6">Asia/Dhaka · ৳ BDT</p>
    </div>
</body>
</html>
