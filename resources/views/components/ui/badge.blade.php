@props(['color' => 'blue'])

@php
    $colors = [
        'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
        'green' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'red' => 'bg-red-50 text-red-600 border-red-200',
        'yellow' => 'bg-amber-50 text-amber-600 border-amber-200',
        'slate' => 'bg-slate-50 text-slate-600 border-slate-200',
    ];
    $theme = $colors[$color] ?? $colors['blue'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border $theme"]) }}>
    {{ $slot }}
</span>
