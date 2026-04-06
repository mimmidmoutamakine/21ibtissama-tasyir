<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e57a4]">{{ $beneficiary->internal_code }}</p>
                <h1 class="text-3xl font-extrabold text-slate-900">{{ $beneficiary->full_name }}</h1>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($beneficiary->whatsapp_father_url)
                    <a href="{{ $beneficiary->whatsapp_father_url }}" target="_blank" class="quick-action">واتساب الأب</a>
                @endif

                @if($beneficiary->whatsapp_mother_url)
                    <a href="{{ $beneficiary->whatsapp_mother_url }}" target="_blank" class="quick-action">واتساب الأم</a>
                @endif

                @if($beneficiary->whatsapp_guardian_url)
                    <a href="{{ $beneficiary->whatsapp_guardian_url }}" target="_blank" class="quick-action">واتساب الولي</a>
                @endif

                @if($canEditBeneficiary)
                    <a href="{{ route('beneficiaries.edit', $beneficiary) }}" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        تعديل المستفيد
                    </a>
                @endif

                <a href="{{ route('reports.monthly', $beneficiary) }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                    طباعة التقرير الشهري
                </a>
            </div>
        </div>
    </x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
        <div class="space-y-6">
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">الملف المركزي</h2>
                <div class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                    <div class="info-card"><span>تاريخ ومكان الازدياد</span><strong>{{ optional($beneficiary->date_of_birth)->format('Y-m-d') }} - {{ $beneficiary->place_of_birth }}</strong></div>
                    <div class="info-card"><span>العنوان</span><strong>{{ $beneficiary->address }}</strong></div>
                    <div class="info-card"><span>المؤطر الرئيسي</span><strong>{{ $beneficiary->primaryEducator?->name ?? 'غير معين' }}</strong></div>
                    <div class="info-card"><span>الخدمات المعينة</span><strong>{{ $beneficiary->serviceAssignments->pluck('service.name')->implode('، ') ?: 'لا توجد' }}</strong></div>
                    <div class="info-card"><span>الحالة الدراسية</span><strong>{{ data_get($beneficiary->schooling_details, 'level', 'غير محدد') }}</strong></div>
                    <div class="info-card"><span>نوع الاستفادة</span><strong>{{ $beneficiary->benefit_type ?? 'غير محدد' }}</strong></div>
                    <div class="info-card"><span>هاتف الأب</span><strong>{{ $beneficiary->father_phone ?: 'غير متوفر' }}</strong></div>
                    <div class="info-card"><span>هاتف الأم</span><strong>{{ $beneficiary->mother_phone ?: 'غير متوفر' }}</strong></div>
                    <div class="info-card"><span>هاتف ولي الأمر</span><strong>{{ $beneficiary->guardian_phone ?: 'غير متوفر' }}</strong></div>
                    <div class="info-card"><span>حالة المستفيد</span><strong>{{ $beneficiary->is_active ? 'نشيط' : 'غادر' }}</strong></div>
                    <div class="info-card"><span>هل المؤطر مخول بالتعديل؟</span><strong>{{ $beneficiary->can_assigned_educator_edit ? 'نعم' : 'لا' }}</strong></div>
                    <div class="info-card"><span>تاريخ المغادرة</span><strong>{{ optional($beneficiary->exit_date)->format('Y-m-d') ?: '—' }}</strong></div>
                    <div class="info-card md:col-span-2"><span>سبب المغادرة</span><strong>{{ $beneficiary->left_reason ?: '—' }}</strong></div>
                </div>
            </div>

            <@php
                $documents = $beneficiary->documents
                    ->sortBy(fn ($doc) => array_search($doc->category, [
                        'medical_file',
                        'psychological_file',
                        'educational_file',
                        'individual_project',
                        'mother_id_copy',
                        'father_id_copy',
                        'guardian_commitment',
                    ]));

                $totalDocuments = $documents->count();
                $uploadedDocuments = $documents->whereNotNull('file_path')->count();
                $documentsProgress = $totalDocuments > 0 ? round(($uploadedDocuments / $totalDocuments) * 100) : 0;
            @endphp

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">وثائق مشروع تحسين ظروف التمدرس</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $uploadedDocuments }} من {{ $totalDocuments }} وثائق مرفوعة
                        </p>
                    </div>

                    <div class="min-w-[140px]">
                        <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-500">
                            <span>نسبة الاكتمال</span>
                            <span>{{ $documentsProgress }}%</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-[#1e57a4] transition-all duration-300"
                                style="width: {{ $documentsProgress }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($documents as $document)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-extrabold text-slate-900">{{ $document->title }}</p>

                                        @if($document->is_required)
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                                إلزامية
                                            </span>
                                        @endif

                                        @if($document->file_path)
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                                مرفوعة
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                                غير مرفوعة
                                            </span>
                                        @endif
                                    </div>

                                    @if($document->notes)
                                        <p class="mt-2 text-xs text-slate-500">{{ $document->notes }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if($document->file_path)
                                        <a href="{{ asset('storage/' . $document->file_path) }}"
                                        target="_blank"
                                        class="quick-action">
                                            فتح الملف
                                        </a>
                                    @endif

                                    @if($canEditBeneficiary)
                                        <a href="{{ route('beneficiaries.edit', $beneficiary) }}#documents"
                                        class="quick-action">
                                            تعديل
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">المشروع الفردي السنوي</h2>
                <div class="mt-5 space-y-4" x-data="{ openBeneficiary: 'project-{{ optional($beneficiary->annualProjects->first())->id }}' }">
                    @foreach ($beneficiary->annualProjects as $project)
                        <div class="rounded-3xl border border-slate-100">
                            <button type="button" class="flex w-full items-center justify-between px-5 py-4 text-right" @click="openBeneficiary = openBeneficiary === 'project-{{ $project->id }}' ? '' : 'project-{{ $project->id }}'">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $project->year_label }}</p>
                                    <p class="text-sm text-slate-500">{{ $project->schooling_space_type }}</p>
                                </div>
                                <x-status-badge :value="$project->approval_status" />
                            </button>
                            <div x-show="openBeneficiary === 'project-{{ $project->id }}'" class="border-t border-slate-100 px-5 py-5">
                                @foreach ($project->domains as $domain)
                                    <div class="mb-4 rounded-2xl bg-slate-50 p-4" x-data="{ openDomain: false }">
                                        <button type="button" class="flex w-full items-center justify-between text-right" @click="openDomain = !openDomain">
                                            <span class="font-bold">{{ $domain->name }}</span>
                                            <span class="text-sm text-slate-500">أهداف {{ $domain->objectives->count() }}</span>
                                        </button>
                                        <div x-show="openDomain" class="mt-4 space-y-3">
                                            @foreach ($domain->objectives as $objective)
                                                <div class="rounded-2xl bg-white px-4 py-3">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <p class="font-semibold text-slate-900">{{ $objective->objective_text }}</p>
                                                        <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">{{ $objective->progress_percent }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">الخطة الشهرية والتقييم</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($beneficiary->monthlyPlans as $plan)
                        <div class="rounded-2xl border border-slate-100 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $plan->month_date->translatedFormat('F Y') }}</p>
                                    <p class="text-sm text-slate-500">{{ $plan->items->count() }} أهداف شهرية</p>
                                </div>
                                <x-status-badge :value="$plan->status" />
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('monthly-plans.show', $plan) }}" class="quick-action">الخطة</a>
                                <a href="{{ route('daily-evaluations.show', $plan) }}" class="quick-action">التقييم اليومي</a>
                                <a href="{{ route('weekly-evaluations.show', $plan) }}" class="quick-action">الأسبوعي</a>
                                <a href="{{ route('monthly-evaluations.show', $plan) }}" class="quick-action">الشهري</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">الاجتماعات الدورية</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($beneficiary->periodicMeetings as $meeting)
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="font-bold">{{ $meeting->meeting_month->translatedFormat('F Y') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $meeting->general_notes }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <h2 class="text-xl font-bold text-slate-900">تقارير المختصين</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($beneficiary->specialistReports as $report)
                        <div class="rounded-2xl border border-slate-100 px-4 py-4">
                            <p class="font-bold">{{ $report->specialty }}</p>
                            <p class="text-sm text-slate-500">{{ $report->specialist?->name }} - {{ $report->report_date?->format('Y-m-d') }}</p>
                            <p class="mt-2 text-sm">{{ $report->summary }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>