<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-950">التقييم اليومي</h1>
                <p class="mt-2 text-base font-bold text-slate-500">{{ $evaluation->beneficiary->full_name }}</p>
            </div>

            <form method="GET" action="{{ route('daily-evaluations.show', $monthlyPlan) }}">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate->toDateString() }}"
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

    <form
        method="POST"
        action="{{ route('daily-evaluations.update', $monthlyPlan) }}"
        class="space-y-5"
        x-data="{ ...dailyEvaluationPage(), isDesktop: window.innerWidth >= 1024 }"
        x-init="init(); window.addEventListener('resize', () => isDesktop = window.innerWidth >= 1024)"
        @progress-update.window="updateProgress()"
    >
        @csrf
        <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">
        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">

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
                    <section x-data="{ open: true }" class="overflow-hidden rounded-[20px] bg-white shadow-soft ring-1 ring-slate-100">
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
                            <div class="mb-3 hidden rounded-[18px] bg-slate-50 px-4 py-3 lg:grid lg:grid-cols-[minmax(220px,1fr)_repeat(5,42px)_135px] lg:items-center lg:gap-3">
                                <div class="text-right text-lg font-black text-slate-900">الهدف الشهري</div>
                                <div class="text-center text-sm font-black text-slate-700">م1</div>
                                <div class="text-center text-sm font-black text-slate-700">م2</div>
                                <div class="text-center text-sm font-black text-slate-700">م3</div>
                                <div class="text-center text-sm font-black text-slate-700">م4</div>
                                <div class="text-center text-sm font-black text-slate-700">م5</div>
                                <div class="text-right text-sm font-black text-slate-500">ملاحظة</div>
                            </div>

                            <div class="space-y-3">
                                @foreach ($entries as $entry)
                                    @php
                                        $goalText = $entry->monthlyPlanItem->monthly_objective ?? '—';
                                    @endphp

                                    <div class="rounded-[16px] bg-slate-50 px-3 py-2 transition hover:bg-slate-100">
                                        {{-- desktop --}}
                                        <div class="hidden lg:grid lg:grid-cols-[minmax(220px,1fr)_repeat(5,42px)_135px] lg:items-center lg:gap-3">
                                            <div class="text-right" dir="rtl">
                                                <div class="text-base font-black leading-6 text-slate-900">
                                                    {{ $goalText }}
                                                </div>
                                            </div>

                                            @for ($attemptNumber = 1; $attemptNumber <= 5; $attemptNumber++)
                                                @php
                                                    $attempt = $entry->attempts->firstWhere('attempt_number', $attemptNumber);
                                                    $state = $attempt?->status_color ?? '';
                                                @endphp

                                                <div x-data="attemptCell(@js($state))" class="flex justify-center">
                                                    <input
                                                        type="hidden"
                                                        name="attempts[{{ $entry->id }}][{{ $attemptNumber }}]"
                                                        :value="state"
                                                        :disabled="!isDesktop"
                                                    >

                                                    <button
                                                        type="button"
                                                        @click="cycle(); $nextTick(() => $dispatch('progress-update'))"
                                                        class="h-9 w-9 rounded-full border-2 transition duration-150 hover:scale-105 active:scale-95"
                                                        :class="circleClass()"
                                                    ></button>
                                                </div>
                                            @endfor

                                            <div>
                                                <input
                                                    type="text"
                                                    name="entry_notes[{{ $entry->id }}]"
                                                    value="{{ old('entry_notes.' . $entry->id, $entry->note) }}"
                                                    :disabled="!isDesktop"
                                                    placeholder="ملاحظة صغيرة..."
                                                    dir="rtl"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                                                >
                                            </div>
                                        </div>

                                        {{-- mobile --}}
                                        <div class="space-y-3 lg:hidden">
                                            <div class="text-right" dir="rtl">
                                                <div class="text-base font-black leading-6 text-slate-900">
                                                    {{ $goalText }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-5 gap-2">
                                                @for ($attemptNumber = 1; $attemptNumber <= 5; $attemptNumber++)
                                                    @php
                                                        $attempt = $entry->attempts->firstWhere('attempt_number', $attemptNumber);
                                                        $state = $attempt?->status_color ?? '';
                                                    @endphp

                                                    <div class="flex flex-col items-center gap-2">
                                                        <span class="text-xs font-black text-slate-500">م{{ $attemptNumber }}</span>

                                                        <div x-data="attemptCell(@js($state))" class="flex justify-center">
                                                            <input
                                                                type="hidden"
                                                                name="attempts[{{ $entry->id }}][{{ $attemptNumber }}]"
                                                                :value="state"
                                                                :disabled="isDesktop"
                                                            >

                                                            <button
                                                                type="button"
                                                                @click="cycle(); $nextTick(() => $dispatch('progress-update'))"
                                                                class="h-9 w-9 rounded-full border-2 transition duration-150 hover:scale-105 active:scale-95"
                                                                :class="circleClass()"
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
                                                    :disabled="isDesktop"
                                                    placeholder="ملاحظة صغيرة حول هذا الهدف..."
                                                    dir="rtl"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
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
                        الملاحظات الصغيرة كتكون تحت كل هدف.
                    </p>

                    <button
                        type="submit"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-[18px] bg-indigo-600 px-5 py-3 text-base font-black text-white transition hover:bg-indigo-700"
                    >
                        حفظ التقييم
                    </button>
                </section>
            </aside>
        </div>
    </form>
</x-app-layout>