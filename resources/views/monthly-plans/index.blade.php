<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-extrabold text-slate-900">الخطة الشهرية</h1>
        </div>
    </x-slot>

    <div class="rounded-[28px] bg-white p-6 shadow-soft">
        @if (session('status'))
            <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">المستفيد</th>
                        <th class="pb-3">الفترة</th>
                        <th class="pb-3">المؤطر</th>
                        <th class="pb-3">الحالة</th>
                        <th class="pb-3">آخر مراجعة</th>
                        <th class="pb-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($plans as $plan)
                        <tr class="transition hover:bg-slate-50">
                            <td class="py-4 font-semibold">{{ $plan->beneficiary->full_name }}</td>
                            <td class="py-4">{{ $plan->month_date->translatedFormat('F Y') }}</td>
                            <td class="py-4">{{ $plan->educator->name }}</td>
                            <td class="py-4">
                                <x-status-badge :value="$plan->status" />
                            </td>
                            <td class="py-4">
                                {{ optional($plan->reviews->first())->reviewed_at?->diffForHumans() ?? 'لا توجد' }}
                            </td>
                            <td class="py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('monthly-plans.show', $plan) }}"
                                       class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                        عرض
                                    </a>

                                    @if (auth()->user()->hasAnyRole(['educator', 'admin']) && $plan->isEditableWindow())
                                        <a href="{{ route('monthly-plans.edit', $plan) }}"
                                           class="rounded-xl bg-[#1e57a4] px-3 py-2 text-xs font-bold text-white transition hover:opacity-90">
                                            تعديل
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                لا توجد خطط شهرية حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $plans->links() }}
        </div>
    </div>
</x-app-layout>