@props(['type' => 'text', 'name', 'id' => null, 'value' => '', 'placeholder' => ''])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $id ?? $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full px-4 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 bg-white shadow-sm']) }}
>
