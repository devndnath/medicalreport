@props(['title' => null, 'actions' => null])
<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-sm']) }}>
    @if($title)
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
            @if($actions)<div>{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
