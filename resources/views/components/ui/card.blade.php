<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-slate-200 shadow-sm p-6 card-hover relative overflow-hidden']) }}>
    <div class="relative z-10">{{ $slot }}</div>
</div>
