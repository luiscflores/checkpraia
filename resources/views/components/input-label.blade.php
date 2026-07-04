@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-wider text-slate-400 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
