<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e57a4]">لوحة قيادة 21 ابتسامة</p>
                <h1 class="text-3xl font-extrabold text-slate-900">متابعة يومية شاملة للمستفيدين والفرق</h1>
            </div>
            <div class="rounded-3xl bg-[#e9b629]/15 px-5 py-3 text-sm font-semibold text-[#1e57a4]">
                {{ now()->translatedFormat('l d F Y') }}
            </div>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-stat-card title="إجمالي المستفيدين" :value="$data['beneficiariesCount']" hint="ضمن نطاق صلاحياتك الحالية" />
        <x-stat-card title="الطلبات الجديدة" :value="$data['newLeads']" tone="gold" />
        <x-stat-card title="الملفات غير المكتملة" :value="$data['incompleteDossiers']" />
        <x-stat-card title="الخطط بانتظار المراجعة" :value="$data['pendingPlans']" tone="green" />
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.4fr,1fr]">
        <div class="rounded-[28px] bg-white p-6 shadow-soft">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">المستفيدون والوصول السريع</h2>
                <a href="{{ route('beneficiaries.index') }}" class="text-sm font-bold text-[#1e57a4]">عرض الكل</a>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($data['beneficiaries'] as $beneficiary)
                    <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="rounded-3xl border border-slate-100 p-4 transition hover:-translate-y-1 hover:border-[#1e57a4]/20 hover:shadow-soft">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-lg font-bold text-slate-900">{{ $beneficiary->full_name }}</p>
                                <p class="text-sm text-slate-500">{{ $beneficiary->internal_code }}</p>
                            </div>
                            <x-status-badge :value="$beneficiary->dossier_status" />
                        </div>
                        <div class="mt-4 h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-[#1e57a4]" style="width: {{ $beneficiary->dossier_completion_percent }}%"></div>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">اكتمال الملف: {{ $beneficiary->dossier_completion_percent }}%</p>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">تنبيهات تشغيلية</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span>خطط مرفوضة وتحتاج تعديل</span><strong>{{ $data['rejectedPlans'] }}</strong></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span>تقييمات متأخرة</span><strong>{{ $data['overdueEvaluations'] }}</strong></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span>مراجعات فصلية قريبة</span><strong>{{ $data['upcomingReviews'] }}</strong></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span>تنبيهات المخزون</span><strong>{{ $data['stockAlerts'] }}</strong></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span>مشاريع نشطة</span><strong>{{ $data['activeProjects'] }}</strong></div>
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">آخر الأنشطة</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($data['recentActivity'] as $activity)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3 text-sm">
                            <p class="font-semibold text-slate-900">{{ $activity->description }}</p>
                            <p class="mt-1 text-slate-500">{{ optional($activity->causer)->name }} - {{ $activity->created_at?->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد أنشطة بعد.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
