{{-- Expects $preset, $from, $to and any extra hidden fields the caller needs preserved (e.g. group_by). --}}
<div class="flex flex-wrap items-end gap-2 mb-6">
    @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month'] as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['period' => $key, 'from' => null, 'to' => null]) }}"
           class="text-xs px-3 py-1.5 rounded-full border {{ $preset === $key ? 'bg-navy-900 text-white border-navy-900' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
    <form method="GET" class="flex items-end gap-2">
        @foreach(request()->except(['period', 'from', 'to']) as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <input type="hidden" name="period" value="custom">
        <div><label class="block text-[11px] text-slate-500">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-1.5"></div>
        <div><label class="block text-[11px] text-slate-500">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-slate-300 text-sm px-3 py-1.5"></div>
        <button class="text-xs px-3 py-1.5 rounded-full border {{ $preset === 'custom' ? 'bg-navy-900 text-white border-navy-900' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">Custom</button>
    </form>
</div>
