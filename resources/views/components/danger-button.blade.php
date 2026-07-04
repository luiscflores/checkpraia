<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center bg-rose-600 hover:bg-rose-500 active:scale-95 text-white px-5 py-3.5 rounded-xl border border-rose-500/30 text-xs font-bold uppercase tracking-wider transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/20 shadow-lg shadow-rose-500/10']) }}>
    {{ $slot }}
</button>
