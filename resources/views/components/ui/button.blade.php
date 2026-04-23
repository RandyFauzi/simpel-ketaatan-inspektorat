@props(['variant' => 'primary'])

@php
    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm',
        'outline' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 shadow-sm',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
        'success' => 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-sm',
    ];
    $style = $variants[$variant] ?? $variants['primary'];
@endphp

<button {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-xl transition-all outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 disabled:opacity-50 disabled:cursor-not-allowed $style"]) }}>
    {{ $slot }}
</button>
