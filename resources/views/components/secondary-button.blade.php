<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center bg-slate-800 hover:bg-slate-700/60 border border-slate-700/60 text-slate-300 hover:text-white px-5 py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all focus:outline-none active:scale-95 shadow-sm']) }}>
    {{ $slot }}
</button>
