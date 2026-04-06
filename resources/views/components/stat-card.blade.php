@props(['title', 'value', 'hint' => null, 'tone' => 'blue'])

<div @class([
    'rounded-[28px] border px-5 py-5 shadow-soft',
    'border-[#1e57a4]/10 bg-white' => $tone === 'blue',
    'border-[#e9b629]/20 bg-[#e9b629]/10' => $tone === 'gold',
    'border-emerald-200 bg-emerald-50' => $tone === 'green',
])>
    <p class="text-sm font-semibold text-slate-500">{{ $title }}</p>
    <p class="mt-4 text-3xl font-extrabold text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
