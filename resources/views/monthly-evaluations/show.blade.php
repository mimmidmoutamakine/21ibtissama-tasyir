<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-950">التقييم الشهري</h1>
                <p class="mt-2 text-base font-bold text-slate-500">{{ $evaluation->beneficiary->full_name }}</p>
            </div>

            <form method="GET" action="{{ route('monthly-evaluations.show', $monthlyPlan) }}">
                <input
                    type="month"
                    name="month"
                    value="{{ $selectedMonth->format('Y-m') }}"
                    onchange="this.form.submit()"
                    class="rounded-full border border-sky-200 bg-sky-100 px-5 py-3 text-base font-black text-sky-700 outline-none"
                >
            </form>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('monthly-evaluations.update', $monthlyPlan) }}" class="space-y-5">
        @csrf
        <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">
        <input type="hidden" name="month" value="{{ $selectedMonth->format('Y-m') }}">

        @foreach ($groupedEntries as $domain => $entries)
            <section class="overflow-hidden rounded-[22px] bg-white shadow-soft ring-1 ring-slate-100">
                <div class="px-5 py-4">
                    <h2 class="text-xl font-black text-slate-950 text-right">{{ $domain }}</h2>
                    <p class="mt-1 text-xs text-slate-500 text-right">{{ $entries->count() }} هدف</p>
                </div>

                <div class="px-4 pb-4">
                    <div class="mb-3 hidden rounded-[16px] bg-slate-50 px-4 py-3 lg:grid lg:grid-cols-[minmax(260px,1fr)_110px_minmax(220px,1fr)_340px] lg:items-center lg:gap-3">
                        <div class="text-right text-sm font-black text-slate-900">الهدف + معيار الإنجاز</div>
                        <div class="text-center text-sm font-black text-slate-700">مستوى الإنجاز</div>
                        <div class="text-right text-sm font-black text-slate-700">ملاحظات المؤطر</div>
                        <div class="text-right text-sm font-black text-slate-700">القرار المقترح</div>
                    </div>

                    <div class="space-y-3">
                        @foreach ($entries as $entry)
                            @php
                                $percent = (int) ($entry->calculated_progress_percent ?? 0);

                                $levelLabel = match (true) {
                                    $percent <= 0 => 'غير منجز',
                                    $percent <= 25 => 'ضعيف',
                                    $percent <= 50 => 'في طور الإنجاز',
                                    $percent <= 75 => 'جيد',
                                    default => 'متقن',
                                };

                                $levelClass = match (true) {
                                    $percent <= 0 => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    $percent <= 25 => 'bg-rose-100 text-rose-700 ring-rose-200',
                                    $percent <= 50 => 'bg-sky-100 text-sky-700 ring-sky-200',
                                    $percent <= 75 => 'bg-amber-100 text-amber-700 ring-amber-200',
                                    default => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                                };

                                $decision = old("entries.{$entry->id}.specialist_final_decision", $entry->specialist_final_decision);
                            @endphp

                            <div class="rounded-[16px] bg-slate-50 px-4 py-4">
                                <div class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_110px_minmax(220px,1fr)_340px] lg:items-center">
                                    <div class="text-right" dir="rtl">
                                        <div class="text-base font-black leading-6 text-slate-950">
                                            {{ $entry->monthlyPlanItem->monthly_objective }}
                                        </div>
                                    </div>

                                    <div class="flex justify-center">
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $levelClass }}">
                                            <span>{{ $percent }}%</span>
                                            <span>•</span>
                                            <span>{{ $levelLabel }}</span>
                                        </span>
                                    </div>

                                    <div>
                                        <input
                                            type="text"
                                            name="entries[{{ $entry->id }}][educator_remarks]"
                                            value="{{ old("entries.{$entry->id}.educator_remarks", $entry->educator_remarks) }}"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                                            placeholder="ملاحظات المؤطر"
                                            dir="rtl"
                                        >
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap justify-end gap-2" dir="rtl">
                                            @foreach (config('association.monthly_final_decisions', []) as $key => $label)
                                                <label class="cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        name="entries[{{ $entry->id }}][specialist_final_decision]"
                                                        value="{{ $key }}"
                                                        class="peer sr-only"
                                                        @checked($decision === $key)
                                                    >
                                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition peer-checked:border-indigo-300 peer-checked:bg-indigo-100 peer-checked:text-indigo-700">
                                                        {{ $label }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>

                                        @if (auth()->user()->hasAnyRole(['specialist', 'admin']))
                                            <input
                                                name="entries[{{ $entry->id }}][specialist_remarks]"
                                                value="{{ old("entries.{$entry->id}.specialist_remarks", $entry->specialist_remarks) }}"
                                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                                                placeholder="ملاحظة المختص"
                                                dir="rtl"
                                            >
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <section class="rounded-[22px] bg-white p-5 shadow-soft ring-1 ring-slate-100">
            <label class="mb-3 block text-lg font-black text-slate-950 text-right">الخلاصة الشهرية العامة</label>
            <textarea
                name="general_remarks"
                class="min-h-24 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                placeholder="خلاصة شهرية عامة..."
                dir="rtl"
            >{{ old('general_remarks', $evaluation->general_remarks) }}</textarea>

            <div class="mt-5 flex justify-end">
                <button class="rounded-2xl bg-[#1e57a4] px-6 py-3 text-sm font-black text-white transition hover:bg-[#184a8b]">
                    حفظ التقييم الشهري
                </button>
            </div>
        </section>
    </form>
</x-app-layout>