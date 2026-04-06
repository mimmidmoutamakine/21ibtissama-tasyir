<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-950">التقييم الأسبوعي</h1>
                <p class="mt-2 text-base font-bold text-slate-500">
                    {{ $evaluation->beneficiary->full_name }} - {{ $monthlyPlan->month_date->translatedFormat('F Y') }}
                </p>
            </div>
            <form method="GET" action="{{ route('weekly-evaluations.show', $monthlyPlan) }}">
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

    <form method="POST"
          action="{{ route('weekly-evaluations.update', $monthlyPlan) }}"
          class="space-y-5"
          x-data="weeklyEvaluationPage()"
          x-init="init()"
          @progress-update.window="updateProgress()">
        @csrf
        <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">
        <input type="hidden" name="month" value="{{ $selectedMonth->format('Y-m') }}">
        
        <section class="rounded-[22px] bg-white px-5 py-4 shadow-soft ring-1 ring-slate-100">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">نسبة تعبئة التقييم</h2>
                <span class="text-sm font-black text-slate-600" x-text="progress + '%'">0%</span>
            </div>

            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-indigo-600 transition-all duration-200" :style="`width: ${progress}%`"></div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_220px]">
            <div class="space-y-4">
                @foreach ($groupedEntries as $domain => $entries)
                    <section x-data="{ open: true }" class="overflow-hidden rounded-[24px] bg-white shadow-soft ring-1 ring-slate-100">
                        <div class="flex items-center justify-between px-5 py-4">
                            <button
                                type="button"
                                @click="open = !open"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                            >
                                <span x-show="open">−</span>
                                <span x-show="!open">+</span>
                            </button>

                            <div class="text-right">
                                <h2 class="text-xl font-black text-slate-950">{{ $domain }}</h2>
                                <p class="mt-1 text-xs text-slate-500">{{ $entries->count() }} هدف</p>
                            </div>
                        </div>

                        <div x-show="open" x-collapse class="px-4 pb-4">
                            <div class="mb-3 hidden rounded-[18px] bg-slate-50 px-4 py-3 lg:grid lg:grid-cols-[minmax(260px,1fr)_repeat(4,52px)_170px] lg:items-center lg:gap-3">
                                <div class="text-right text-lg font-black text-slate-900">الهدف الشهري</div>
                                <div class="text-center text-base font-black text-slate-700">أ1</div>
                                <div class="text-center text-base font-black text-slate-700">أ2</div>
                                <div class="text-center text-base font-black text-slate-700">أ3</div>
                                <div class="text-center text-base font-black text-slate-700">أ4</div>
                                <div class="text-right text-sm font-black text-slate-500">ملاحظة</div>
                            </div>

                            <div class="space-y-3">
                                @foreach ($entries as $entry)
                                    @php
                                        $goalText = $entry->monthlyPlanItem->monthly_objective ?? '—';
                                        $weekStates = [
                                            1 => $entry->week_1_status_color ?? '',
                                            2 => $entry->week_2_status_color ?? '',
                                            3 => $entry->week_3_status_color ?? '',
                                            4 => $entry->week_4_status_color ?? '',
                                        ];
                                    @endphp
                                    <div
                                        class="rounded-[18px] bg-slate-50 p-3 transition hover:bg-slate-100"
                                        x-data="{
                                            weeks: {
                                                1: @js($weekStates[1] ?? ''),
                                                2: @js($weekStates[2] ?? ''),
                                                3: @js($weekStates[3] ?? ''),
                                                4: @js($weekStates[4] ?? '')
                                            },
                                            states: ['', 'red', 'blue', 'yellow', 'green'],
                                            cycle(weekNumber) {
                                                const current = this.weeks[weekNumber] ?? '';
                                                const index = this.states.indexOf(current);
                                                this.weeks[weekNumber] = this.states[(index + 1) % this.states.length];
                                                this.$nextTick(() => this.$dispatch('progress-update'));
                                            },
                                            circleClass(weekNumber) {
                                                switch (this.weeks[weekNumber]) {
                                                    case 'red':
                                                        return 'border-rose-500 bg-rose-500 shadow-[0_6px_18px_rgba(244,63,94,0.22)]';
                                                    case 'blue':
                                                        return 'border-sky-500 bg-sky-500 shadow-[0_6px_18px_rgba(14,165,233,0.22)]';
                                                    case 'yellow':
                                                        return 'border-amber-400 bg-amber-400 shadow-[0_6px_18px_rgba(251,191,36,0.20)]';
                                                    case 'green':
                                                        return 'border-emerald-500 bg-emerald-500 shadow-[0_6px_18px_rgba(16,185,129,0.22)]';
                                                    default:
                                                        return 'border-slate-300 bg-white';
                                                }
                                            }
                                        }"
                                    >
                                        @for ($weekNumber = 1; $weekNumber <= 4; $weekNumber++)
                                            <input
                                                type="hidden"
                                                name="weeks[{{ $entry->id }}][{{ $weekNumber }}]"
                                                x-model="weeks[{{ $weekNumber }}]"
                                            >
                                        @endfor

                                        <div class="hidden lg:grid lg:grid-cols-[minmax(260px,1fr)_repeat(4,52px)_170px] lg:items-center lg:gap-3">
                                            <div class="text-right" dir="rtl">
                                                <div class="text-lg font-black leading-7 text-slate-900">
                                                    {{ $goalText }}
                                                </div>
                                            </div>

                                            @for ($weekNumber = 1; $weekNumber <= 4; $weekNumber++)
                                                <div class="flex justify-center">
                                                    <button
                                                        type="button"
                                                        @click="cycle({{ $weekNumber }})"
                                                        class="h-9 w-9 rounded-full border-2 transition duration-150 hover:scale-105 active:scale-95"
                                                        :class="circleClass({{ $weekNumber }})"
                                                    ></button>
                                                </div>
                                            @endfor

                                            <div>
                                                <input
                                                    type="text"
                                                    name="entry_notes[{{ $entry->id }}]"
                                                    value="{{ old('entry_notes.' . $entry->id, $entry->note) }}"
                                                    placeholder="ملاحظة صغيرة..."
                                                    dir="rtl"
                                                    class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100"
                                                >
                                            </div>
                                        </div>

                                        <div class="space-y-3 lg:hidden">
                                            <div class="text-right" dir="rtl">
                                                <div class="text-lg font-black leading-7 text-slate-900">
                                                    {{ $goalText }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-4 gap-2">
                                                @for ($weekNumber = 1; $weekNumber <= 4; $weekNumber++)
                                                    <div class="flex flex-col items-center gap-2">
                                                        <span class="text-xs font-black text-slate-500">أ{{ $weekNumber }}</span>

                                                        <div class="flex justify-center">
                                                            <button
                                                                type="button"
                                                                @click="cycle({{ $weekNumber }})"
                                                                class="h-9 w-9 rounded-full border-2 transition duration-150 hover:scale-105 active:scale-95"
                                                                :class="circleClass({{ $weekNumber }})"
                                                            ></button>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>

                                            <div>
                                                <input
                                                    type="text"
                                                    name="entry_notes[{{ $entry->id }}]"
                                                    value="{{ old('entry_notes.' . $entry->id, $entry->note) }}"
                                                    placeholder="ملاحظة صغيرة حول هذا الهدف..."
                                                    dir="rtl"
                                                    class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <aside>
                <section class="rounded-[20px] bg-white p-4 shadow-soft ring-1 ring-slate-100 xl:sticky xl:top-24">
                    <h2 class="text-xl font-bold text-slate-950">حفظ</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                        القيم محسوبة تلقائيًا من التقييم اليومي ويمكن تعديلها يدويًا.
                    </p>

                    <button
                        type="submit"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-[18px] bg-indigo-600 px-5 py-3 text-base font-black text-white transition hover:bg-indigo-700"
                    >
                        حفظ التقييم الأسبوعي
                    </button>
                </section>
            </aside>
        </div>
    </form>
</x-app-layout>