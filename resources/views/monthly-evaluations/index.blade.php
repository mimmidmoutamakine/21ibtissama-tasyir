<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">التقييم الشهري</h1>
            <p class="mt-1 text-sm text-slate-500">فتح ورقة التقييم الشهري لكل خطة، مع نسب الإنجاز المحسوبة وقرارات المختص.</p>
        </div>
    </x-slot>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($plans as $plan)
            <a href="{{ route('monthly-evaluations.show', $plan) }}" class="rounded-[28px] bg-white p-6 shadow-soft transition hover:-translate-y-1">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xl font-extrabold text-slate-900">{{ $plan->beneficiary->full_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $plan->month_date->translatedFormat('F Y') }}</p>
                    </div>
                    <x-status-badge :value="$plan->status" />
                </div>
                <div class="mt-5 space-y-2 text-sm text-slate-600">
                    <p>عدد الأهداف الشهرية: {{ $plan->items->count() }}</p>
                    <p>تتبع الإنجاز الشهري وقرار المختص النهائي.</p>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $plans->links() }}</div>
</x-app-layout>
