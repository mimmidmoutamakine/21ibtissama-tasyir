<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">المخزون</h1>
    </x-slot>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($items as $item)
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-lg font-bold">{{ $item->name }}</p>
                    @if ($item->is_alert)
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">تنبيه</span>
                    @endif
                </div>
                <div class="mt-4 grid gap-2 text-sm text-slate-600">
                    <p>الكمية الحالية: {{ $item->quantity }} {{ $item->unit }}</p>
                    <p>حد التنبيه: {{ $item->alert_threshold }}</p>
                    <p>آخر حركة: {{ optional($item->movements->sortByDesc('movement_date')->first())->movement_type ?? 'لا توجد' }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
