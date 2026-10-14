@props(['label', 'value', 'sub' => null, 'tone' => 'default'])
@php
$toneClasses = [
    'default' => 'text-slate-900',
    'warn' => 'text-amber-600',
    'danger' => 'text-rose-600',
    'good' => 'text-emerald-600',
];
@endphp
<div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $label }}</p>
    <p class="text-2xl font-semibold mt-1 {{ $toneClasses[$tone] }}">{{ $value }}</p>
    @if($sub)
        <p class="text-xs text-slate-400 mt-1">{{ $sub }}</p>
    @endif
</div>
