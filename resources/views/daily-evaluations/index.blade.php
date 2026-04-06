<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-black text-slate-900">التقييم اليومي</h1>
                <p class="mt-1 text-sm text-slate-500">
                    اختر المستفيد والخطة الشهرية للدخول مباشرة إلى ورقة التقييم.
                </p>
            </div>

            <div class="rounded-2xl bg-white px-4 py-3 shadow-soft ring-1 ring-slate-100">
                <p class="text-xs font-bold text-slate-400">عدد الخطط المعروضة</p>
                <p class="text-2xl font-black text-slate-900">{{ $plans->total() }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($plans as $plan)
            <a
                href="{{ route('daily-evaluations.show', $plan) }}"
                class="group rounded-[28px] bg-white p-6 shadow-soft ring-1 ring-slate-100 transition duration-200 hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xl font-black text-slate-900">{{ $plan->beneficiary->full_name }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            {{ $plan->month_date->translatedFormat('F Y') }}
                        </p>
                    </div>

                    <x-status-badge :value="$plan->status" />
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-bold text-slate-400">الأهداف</p>
                        <p class="mt-1 text-lg font-black text-slate-900">{{ $plan->items->count() }}</p>
                    </div>

                    <div class="rounded-2xl bg-indigo-50 px-4 py-3">
                        <p class="text-xs font-bold text-indigo-400">دخول</p>
                        <p class="mt-1 text-sm font-black text-indigo-700">فتح التقييم</p>
                    </div>
                </div>

                <div class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-indigo-600">
                    <span>الدخول إلى التقييم</span>
                    <span class="transition group-hover:translate-x-[-4px]">←</span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $plans->links() }}</div>
</x-app-layout>