<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e57a4]">{{ $annualProject->beneficiary->full_name }} - {{ $annualProject->beneficiary->internal_code }}</p>
                <h1 class="text-3xl font-extrabold text-slate-900">المشروع الفردي السنوي</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $annualProject->year_label }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-status-badge :value="$annualProject->approval_status" />

                @if ($canEditAnnualProject)
                    <a href="{{ route('annual-projects.edit', $annualProject) }}" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        تعديل المشروع
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

    <section class="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
        <div class="space-y-6">
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">المعطيات العامة</h2>
                <div class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                    <div class="info-card"><span>الموسم / السنة</span><strong>{{ $annualProject->year_label }}</strong></div>
                    <div class="info-card"><span>فضاء التمدرس</span><strong>{{ $annualProject->schooling_space_type ?: 'غير محدد' }}</strong></div>
                    <div class="info-card md:col-span-2"><span>الوضعية الأولية</span><strong>{{ $annualProject->initial_situation_summary ?: 'لا توجد معطيات' }}</strong></div>
                    <div class="info-card md:col-span-2"><span>مشاركة الأسرة</span><strong>{{ $annualProject->family_participation ?: 'لا توجد معطيات' }}</strong></div>
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">الفريق المشارك</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($annualProject->team_participants ?? [] as $participant)
                        <span class="rounded-full bg-[#1e57a4]/10 px-4 py-2 text-sm font-bold text-[#1e57a4]">{{ $participant }}</span>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد أسماء مضافة.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">ربط سريع</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('beneficiaries.show', $annualProject->beneficiary) }}" class="quick-action">ملف المستفيد</a>
                    @foreach ($annualProject->beneficiary->monthlyPlans()->latest('month_date')->take(3)->get() as $plan)
                        <a href="{{ route('monthly-plans.show', $plan) }}" class="quick-action">{{ $plan->month_date->format('Y-m') }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-soft">
            <h2 class="text-xl font-bold text-slate-900">المجالات والأهداف</h2>

            <div class="mt-5 space-y-4" x-data="{ openDomain: @js(optional($annualProject->domains->sortBy('display_order')->first())->id) }">
                @foreach ($annualProject->domains->sortBy('display_order') as $domain)
                    <div class="rounded-3xl border border-slate-100">
                        <button type="button" class="flex w-full items-center justify-between px-5 py-4 text-right" @click="openDomain = openDomain === {{ $domain->id }} ? null : {{ $domain->id }}">
                            <div>
                                <p class="font-bold text-slate-900">{{ $domain->name }}</p>
                                <p class="text-sm text-slate-500">{{ $domain->objectives->count() }} أهداف تشغيلية</p>
                            </div>
                            <span class="rounded-full bg-[#e9b629]/15 px-3 py-1 text-xs font-bold text-[#8a6910]">مجال</span>
                        </button>

                        <div x-show="openDomain === {{ $domain->id }}" class="border-t border-slate-100 px-5 py-5">
                            <div class="space-y-3">
                                @foreach ($domain->objectives->sortBy('display_order') as $objective)
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="space-y-3">
                                                <p class="font-bold text-slate-900">{{ $objective->objective_text }}</p>
                                                <div class="grid gap-3 text-sm md:grid-cols-2">
                                                    <div class="rounded-2xl bg-white p-3">
                                                        <span class="mb-1 block text-xs font-bold text-slate-400">المستوى الأولي</span>
                                                        <strong class="text-slate-700">{{ $objective->baseline_level ?: 'غير محدد' }}</strong>
                                                    </div>
                                                    <div class="rounded-2xl bg-white p-3">
                                                        <span class="mb-1 block text-xs font-bold text-slate-400">المستوى المستهدف</span>
                                                        <strong class="text-slate-700">{{ $objective->target_level ?: 'غير محدد' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="shrink-0 rounded-2xl bg-[#1e57a4]/10 px-4 py-3 text-center">
                                                <span class="block text-xs font-bold text-slate-500">نسبة التقدم</span>
                                                <strong class="text-xl font-extrabold text-[#1e57a4]">{{ $objective->progress_percent }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
