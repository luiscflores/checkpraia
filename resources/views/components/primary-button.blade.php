<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center bg-blue-600 hover:bg-blue-500 active:scale-95 text-white px-5 py-3.5 rounded-xl border border-blue-500/30 text-xs font-bold uppercase tracking-wider transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-lg shadow-blue-500/10']) }}>
    {{ $slot }}
</button>
