<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    الخطة الشهرية - {{ $monthlyPlan->beneficiary->full_name }}
                </h1>
                <p class="text-sm text-slate-500">{{ $monthlyPlan->month_date->translatedFormat('F Y') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <x-status-badge :value="$monthlyPlan->status" />

                @if (auth()->user()->hasAnyRole(['educator', 'admin']) && $monthlyPlan->isEditableWindow())
                    <a href="{{ route('monthly-plans.edit', $monthlyPlan) }}"
                       class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        تعديل الخطة
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-[28px] bg-white p-6 shadow-soft">
            <h2 class="text-xl font-bold">بنود الخطة</h2>

            <div class="mt-5 space-y-4">
                @forelse ($monthlyPlan->items as $item)
                    <div class="rounded-3xl border border-slate-100 p-5">
                        <p class="text-sm font-bold text-[#1e57a4]">{{ $item->domain_axis }}</p>
                        <p class="mt-2 text-lg font-bold text-slate-900">{{ $item->monthly_objective }}</p>

                        <div class="mt-4 grid gap-3 text-sm md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="mb-2 block text-xs font-bold text-slate-400">النشاط</span>
                                <strong class="text-slate-800">{{ $item->activity }}</strong>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="mb-2 block text-xs font-bold text-slate-400">الوسائل</span>
                                <strong class="text-slate-800">{{ $item->resources ?: '—' }}</strong>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <span class="mb-2 block text-xs font-bold text-slate-400">معيار النجاح</span>
                                <strong class="text-slate-800">{{ $item->success_criteria ?: '—' }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-200 p-8 text-center text-slate-500">
                        لا توجد بنود في هذه الخطة بعد.
                    </div>
                @endforelse
            </div>

            @if (auth()->user()->hasAnyRole(['educator', 'admin']) && $monthlyPlan->isEditableWindow())
                <form method="POST" action="{{ route('monthly-plans.submit', $monthlyPlan) }}" class="mt-6">
                    @csrf
                    <button class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        إرسال الخطة للمراجعة
                    </button>
                </form>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold">سجل المراجعات والاعتماد</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($monthlyPlan->reviews as $review)
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-bold">{{ $review->reviewer->name }}</p>
                                <x-status-badge :value="$review->decision" />
                            </div>

                            <p class="mt-3 text-sm text-slate-600">
                                {{ $review->remarks ?: 'لا توجد ملاحظات.' }}
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                التغييرات المطلوبة / الملخص:
                                {{ $review->changes_summary ?: 'لا توجد' }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                {{ $review->reviewed_at?->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-500">
                            لا توجد مراجعات بعد.
                        </div>
                    @endforelse
                </div>
            </div>

            @if (auth()->user()->hasAnyRole(['specialist', 'admin']))
                <form method="POST"
                      action="{{ route('monthly-plans.review', $monthlyPlan) }}"
                      class="rounded-[28px] bg-white p-6 shadow-soft">
                    @csrf

                    <h2 class="text-xl font-bold">قرار المختص</h2>

                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">القرار</label>
                            <select name="decision" class="form-input w-full">
                                <option value="approved">مصادقة</option>
                                <option value="rejected">رفض</option>
                                <option value="revision">إرجاع للتعديل</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">ملاحظات المختص</label>
                            <textarea name="remarks" class="form-input min-h-24 w-full" placeholder="اكتب ملاحظاتك هنا...">{{ old('remarks') }}</textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">التغييرات المطلوبة / ملخص القرار</label>
                            <textarea name="changes_summary" class="form-input min-h-24 w-full" placeholder="ما الذي يجب تغييره؟">{{ old('changes_summary') }}</textarea>
                        </div>
                    </div>

                    <button class="mt-5 rounded-2xl bg-[#e9b629] px-5 py-3 text-sm font-bold text-slate-900">
                        حفظ القرار
                    </button>
                </form>
            @endif
        </div>
    </section>
</x-app-layout>