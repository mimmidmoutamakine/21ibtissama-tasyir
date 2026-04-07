<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e57a4]">{{ $beneficiary->full_name }} - {{ $beneficiary->internal_code }}</p>
                <h1 class="text-3xl font-extrabold text-slate-900">{{ $isCreateMode ? 'إضافة المشروع الفردي السنوي' : 'تعديل المشروع الفردي السنوي' }}</h1>
            </div>
            <a href="{{ $isCreateMode ? route('beneficiaries.show', $beneficiary) : route('annual-projects.show', $annualProject) }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                رجوع
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ $isCreateMode ? route('annual-projects.store', $beneficiary) : route('annual-projects.update', $annualProject) }}" class="space-y-6">
        @csrf
        @unless($isCreateMode)
            @method('PUT')
        @endunless

        <section class="rounded-[28px] bg-white p-6 shadow-soft">
            <h2 class="text-xl font-bold text-slate-900">البيانات العامة</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">الموسم / السنة</label>
                    <input name="year_label" value="{{ old('year_label', $annualProject->year_label) }}" class="form-input w-full" placeholder="مثال: الموسم 2026 - 2027">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">فضاء / نوع التمدرس</label>
                    <input name="schooling_space_type" value="{{ old('schooling_space_type', $annualProject->schooling_space_type) }}" class="form-input w-full" placeholder="قسم إدماج / تمدرس مدرسي / ...">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-600">الوضعية الأولية</label>
                    <textarea name="initial_situation_summary" class="form-input min-h-28 w-full" placeholder="تلخيص لوضعية المستفيد في بداية الموسم...">{{ old('initial_situation_summary', $annualProject->initial_situation_summary) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-600">الفريق المشارك</label>
                    <textarea name="team_participants_text" class="form-input min-h-24 w-full" placeholder="كل اسم في سطر مستقل">{{ old('team_participants_text', implode(PHP_EOL, $annualProject->team_participants ?? [])) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-600">مشاركة الأسرة</label>
                    <textarea name="family_participation" class="form-input min-h-24 w-full" placeholder="كيف تساهم الأسرة في التتبع؟">{{ old('family_participation', $annualProject->family_participation) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">حالة الاعتماد</label>
                    <select name="approval_status" class="form-input w-full">
                        @foreach (['draft' => 'مسودة', 'approved' => 'مصادق عليه', 'archived' => 'مؤرشف'] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(old('approval_status', $annualProject->approval_status ?? 'draft') === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-soft">
            <div>
                <h2 class="text-xl font-bold text-slate-900">المجالات والأهداف</h2>
                <p class="mt-1 text-sm text-slate-500">المجالات ثابتة على شكل accordion، ويمكنك إضافة الأهداف داخل كل مجال.</p>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($domainBlueprints as $domainIndex => $domain)
                    <div class="rounded-3xl border border-slate-100"
                        x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                        <input type="hidden" name="domains[{{ $domainIndex }}][id]" value="{{ old("domains.$domainIndex.id", $domain['id'] ?? '') }}">
                        <input type="hidden" name="domains[{{ $domainIndex }}][name]" value="{{ $domain['name'] }}">
                        <input type="hidden" name="domains[{{ $domainIndex }}][display_order]" value="{{ old("domains.$domainIndex.display_order", $domain['display_order'] ?? ($domainIndex + 1)) }}">

                        <button type="button" class="flex w-full items-center justify-between px-5 py-4 text-right" @click="open = !open">
                            <div>
                                <p class="font-bold text-slate-900">{{ $domain['name'] }}</p>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-2xl font-bold text-slate-700" x-text="open ? '−' : '+'"></span>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-slate-100 px-5 py-5"
                            x-data="annualProjectDomainEditor(@js(old("domains.$domainIndex.objectives", $domain['objectives'] ?? [])))">
                            <div class="mb-4 flex justify-end">
                                <button type="button" class="rounded-2xl bg-[#1e57a4] px-4 py-2 text-sm font-bold text-white" @click="addObjective()">
                                    إضافة هدف
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(objective, objectiveIndex) in objectives" :key="objectiveIndex">
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <input type="hidden" :name="`domains[{{ $domainIndex }}][objectives][${objectiveIndex}][id]`" x-model="objective.id">
                                        <input type="hidden" :name="`domains[{{ $domainIndex }}][objectives][${objectiveIndex}][display_order]`" x-model="objective.display_order">
                                        <input type="hidden" :name="`domains[{{ $domainIndex }}][objectives][${objectiveIndex}][baseline_level]`" x-model="objective.baseline_level">
                                        <input type="hidden" :name="`domains[{{ $domainIndex }}][objectives][${objectiveIndex}][target_level]`" x-model="objective.target_level">

                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <p class="text-sm font-bold text-slate-700">الهدف <span x-text="objectiveIndex + 1"></span></p>
                                            <button type="button"
                                                class="rounded-2xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                @click="removeObjective(objectiveIndex)"
                                                :disabled="objectives.length <= 1">
                                                حذف الهدف
                                            </button>
                                        </div>

                                        <label class="mb-2 block text-sm font-bold text-slate-600">نص الهدف</label>
                                        <textarea class="form-input min-h-24 w-full"
                                            :name="`domains[{{ $domainIndex }}][objectives][${objectiveIndex}][objective_text]`"
                                            x-model="objective.objective_text"
                                            placeholder="أدخل الهدف التشغيلي هنا"></textarea>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                يرجى مراجعة المعطيات، بعض الخانات ما زالت تحتاج التصحيح.
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <button class="rounded-2xl bg-[#1e57a4] px-6 py-3 text-sm font-bold text-white">
                {{ $isCreateMode ? 'حفظ المشروع' : 'حفظ التعديلات' }}
            </button>
            <a href="{{ $isCreateMode ? route('beneficiaries.show', $beneficiary) : route('annual-projects.show', $annualProject) }}" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-700">
                إلغاء
            </a>
        </div>
    </form>
</x-app-layout>
