<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    تعديل الخطة الشهرية - {{ $monthlyPlan->beneficiary->full_name }}
                </h1>
                <p class="text-sm text-slate-500">{{ $monthlyPlan->month_date->translatedFormat('F Y') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <x-status-badge :value="$monthlyPlan->status" />
                <a href="{{ route('monthly-plans.show', $monthlyPlan) }}"
                   class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                    رجوع
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $domains = [
            'التربية والتعلمات المدرسية',
            'التعلمات الاجتماعية',
            'التواصل واللغة',
            'الاستقلالية والاعتماد على النفس',
            'الحركية والإدراك الحسي',
        ];

        $groupedItems = collect($domains)->mapWithKeys(fn ($domain) => [$domain => []])->toArray();

        foreach ($monthlyPlan->items as $item) {
            $domain = $item->domain_axis ?: 'بدون مجال';
            if (!array_key_exists($domain, $groupedItems)) {
                $groupedItems[$domain] = [];
            }

            $groupedItems[$domain][] = [
                'id' => $item->id,
                'annual_project_objective_id' => $item->annual_project_objective_id,
                'domain_axis' => $item->domain_axis,
                'monthly_objective' => $item->monthly_objective,
                'activity' => $item->activity,
                'resources' => $item->resources,
                'success_criteria' => $item->success_criteria,
            ];
        }
    @endphp

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            المرجو تصحيح الأخطاء قبل الحفظ.
        </div>
    @endif

    <form method="POST"
          action="{{ route('monthly-plans.update', $monthlyPlan) }}"
          x-data="monthlyPlanAccordionEditor(@js($groupedItems))"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-[28px] bg-white p-5 shadow-soft">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">بنود الخطة</h2>
                    <p class="mt-1 text-sm text-slate-500">نظم الأهداف حسب المجال، وركز على المطلوب فقط.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit"
                            class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        حفظ الخطة
                    </button>

                    <a href="{{ route('monthly-plans.show', $monthlyPlan) }}"
                       class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                        إلغاء
                    </a>
                </div>
            </div>
        </div>

        <template x-for="(domain, domainIndex) in domains" :key="domain.name">
            <section class="rounded-[28px] bg-white p-4 shadow-soft">
                <button type="button"
                        class="flex w-full items-center justify-between gap-4 rounded-2xl px-2 py-2 text-right"
                        @click="toggleDomain(domainIndex)">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#eef4ff] text-[#1e57a4] font-extrabold">
                            <span x-text="domainIndex + 1"></span>
                        </div>

                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900" x-text="domain.name"></h3>
                            <p class="text-sm text-slate-500">
                                <span x-text="domain.items.length"></span>
                                <span>هدف/أهداف</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                              x-text="domain.items.length ? 'معبأ جزئياً' : 'فارغ'"></span>

                        <svg class="h-5 w-5 text-slate-500 transition"
                             :class="domain.open ? 'rotate-180' : ''"
                             viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 011.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

                <div x-show="domain.open" x-collapse class="mt-4 border-t border-slate-100 pt-4">
                    <div class="mb-4 flex justify-start">
                        <button type="button"
                                @click="addItem(domainIndex)"
                                class="rounded-2xl bg-[#1e57a4] px-4 py-2 text-sm font-bold text-white">
                            + إضافة هدف
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-if="domain.items.length === 0">
                            <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500">
                                لا توجد أهداف داخل هذا المجال بعد.
                            </div>
                        </template>

                        <template x-for="(item, itemIndex) in domain.items" :key="item.key">
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/60 p-4">
                                <input type="hidden" :name="inputName(domainIndex, itemIndex, 'id')" x-model="item.id">
                                <input type="hidden" :name="inputName(domainIndex, itemIndex, 'annual_project_objective_id')" x-model="item.annual_project_objective_id">
                                <input type="hidden" :name="inputName(domainIndex, itemIndex, 'domain_axis')" :value="domain.name">

                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                                        هدف <span x-text="itemIndex + 1"></span>
                                    </div>

                                    <button type="button"
                                            @click="removeItem(domainIndex, itemIndex)"
                                            class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600">
                                        حذف
                                    </button>
                                </div>

                                <div class="grid gap-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700">الهدف الشهري</label>
                                        <textarea class="form-input min-h-[90px] w-full"
                                                  :name="inputName(domainIndex, itemIndex, 'monthly_objective')"
                                                  x-model="item.monthly_objective"
                                                  placeholder="اكتب الهدف الشهري بشكل مختصر وواضح"></textarea>
                                    </div>

                                    <div class="grid gap-4 lg:grid-cols-3">
                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">النشاط</label>
                                            <textarea class="form-input min-h-[90px] w-full"
                                                      :name="inputName(domainIndex, itemIndex, 'activity')"
                                                      x-model="item.activity"
                                                      placeholder="النشاط المقترح"></textarea>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">الوسائل</label>
                                            <textarea class="form-input min-h-[90px] w-full"
                                                      :name="inputName(domainIndex, itemIndex, 'resources')"
                                                      x-model="item.resources"
                                                      placeholder="بطاقات، صور، أدوات..."></textarea>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">معيار النجاح</label>
                                            <textarea class="form-input min-h-[90px] w-full"
                                                      :name="inputName(domainIndex, itemIndex, 'success_criteria')"
                                                      x-model="item.success_criteria"
                                                      placeholder="مؤشر تحقق الهدف"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        </template>

        <div class="rounded-[28px] bg-white p-5 shadow-soft">
            <div class="flex flex-wrap gap-3">
                <button type="submit"
                        class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                    حفظ الخطة
                </button>

                <a href="{{ route('monthly-plans.show', $monthlyPlan) }}"
                   class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                    إلغاء
                </a>
            </div>
        </div>
    </form>

    <div class="mt-6 rounded-[28px] bg-white p-5 shadow-soft">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold">المراجعات السابقة</h2>
                <p class="mt-1 text-sm text-slate-500">يمكنك الاطلاع على الملاحظات قبل إعادة الإرسال.</p>
            </div>

            <form method="POST" action="{{ route('monthly-plans.submit', $monthlyPlan) }}">
                @csrf
                <button class="rounded-2xl bg-[#e9b629] px-5 py-3 text-sm font-bold text-slate-900">
                    إرسال للمراجعة
                </button>
            </form>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($monthlyPlan->reviews as $review)
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold">{{ $review->reviewer->name }}</p>
                        <x-status-badge :value="$review->decision" />
                    </div>

                    <p class="mt-2 text-sm text-slate-600">{{ $review->remarks ?: 'لا توجد ملاحظات.' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $review->changes_summary ?: 'لا توجد تغييرات مطلوبة.' }}</p>
                </div>
            @empty
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-500">
                    لا توجد مراجعات بعد.
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function monthlyPlanAccordionEditor(groupedItems) {
            const domains = Object.keys(groupedItems).map((name, index) => ({
                name,
                open: index === 0,
                items: (groupedItems[name] || []).map((item) => ({
                    key: item.id || (Date.now() + Math.random()),
                    id: item.id ?? '',
                    annual_project_objective_id: item.annual_project_objective_id ?? '',
                    domain_axis: item.domain_axis ?? name,
                    monthly_objective: item.monthly_objective ?? '',
                    activity: item.activity ?? '',
                    resources: item.resources ?? '',
                    success_criteria: item.success_criteria ?? '',
                })),
            }));

            return {
                domains,

                toggleDomain(index) {
                    this.domains[index].open = !this.domains[index].open;
                },

                addItem(domainIndex) {
                    this.domains[domainIndex].items.push({
                        key: Date.now() + Math.random(),
                        id: '',
                        annual_project_objective_id: '',
                        domain_axis: this.domains[domainIndex].name,
                        monthly_objective: '',
                        activity: '',
                        resources: '',
                        success_criteria: '',
                    });

                    this.domains[domainIndex].open = true;
                },

                removeItem(domainIndex, itemIndex) {
                    this.domains[domainIndex].items.splice(itemIndex, 1);
                },

                inputName(domainIndex, itemIndex, field) {
                    let flatIndex = 0;

                    for (let i = 0; i < domainIndex; i++) {
                        flatIndex += this.domains[i].items.length;
                    }

                    flatIndex += itemIndex;

                    return `items[${flatIndex}][${field}]`;
                },
            };
        }
    </script>
</x-app-layout>