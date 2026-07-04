@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm w-full transition-all']) }}>
