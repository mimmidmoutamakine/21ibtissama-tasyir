<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">الحضور والمواظبة</h1>
                <p class="mt-1 text-sm text-slate-500">تسجيل سريع، أوضح، وبعدد نقرات أقل</p>
            </div>

            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                <label for="date" class="text-sm font-bold text-slate-700">التاريخ</label>
                <input
                    id="date"
                    name="date"
                    type="date"
                    value="{{ $attendanceDate }}"
                    class="form-input min-w-[170px]"
                    onchange="this.form.submit()"
                >
            </form>
        </div>
    </x-slot>

    @php
        $beneficiaryStatuses = collect($statuses)->except('leave')->all();
    @endphp

    <section class="grid gap-6 xl:grid-cols-2">
        <form
            method="POST"
            action="{{ route('attendance.beneficiaries.store') }}"
            class="rounded-[28px] bg-white p-5 shadow-soft"
            x-data="attendanceForm()"
        >
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

            <div class="sticky top-3 z-10 mb-5 rounded-[24px] border border-slate-200 bg-white/95 p-4 backdrop-blur">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">حضور المستفيدين</h2>
                            <p class="text-sm text-slate-500">{{ $attendanceDate }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="setAll('present')"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50"
                            >
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.294 6.543-6.544a1 1 0 011.415 0z" clip-rule="evenodd"/>
                                </svg>
                                تحديد الجميع حاضر
                            </button>

                            <button
                                type="button"
                                @click="setAll('absent')"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50"
                            >
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                تحديد الجميع غائب
                            </button>

                            <button
                                type="button"
                                @click="clearAll()"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-200"
                            >
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M5.5 5A2.5 2.5 0 018 2.5h4A2.5 2.5 0 0114.5 5H17a1 1 0 110 2h-1v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 010-2h2.5zM8 4.5a.5.5 0 00-.5.5V5h5v-.5a.5.5 0 00-.5-.5H8z"/>
                                </svg>
                                مسح التحديد
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 rounded-xl bg-yellow-50 px-4 py-2 text-sm font-bold text-yellow-800">
                عدد المستفيدين: {{ $beneficiaries->count() }}
            </div>

            <div class="space-y-3">
                @forelse ($beneficiaries->sortBy('full_name') as $beneficiary)
                    @php
                        $saved = $beneficiaryAttendance[$beneficiary->id] ?? null;
                        $savedStatus = $saved->status ?? 'present';
                        $savedNotes = $saved->notes ?? '';
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition">
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-base font-extrabold text-slate-900">
                                    {{ $beneficiary->full_name }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($beneficiaryStatuses as $key => $label)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="attendance[{{ $beneficiary->id }}][status]"
                                            value="{{ $key }}"
                                            class="hidden peer beneficiary-status"
                                            data-beneficiary-id="{{ $beneficiary->id }}"
                                            {{ $savedStatus === $key ? 'checked' : '' }}
                                        >
                                        <span class="inline-flex rounded-full border px-4 py-2 text-sm font-bold transition
                                            {{ $key === 'present' ? 'border-emerald-200 bg-white text-emerald-700 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white' : '' }}
                                            {{ $key === 'absent' ? 'border-rose-200 bg-white text-rose-700 peer-checked:border-rose-600 peer-checked:bg-rose-600 peer-checked:text-white' : '' }}
                                            {{ $key === 'late' ? 'border-amber-200 bg-white text-amber-700 peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white' : '' }}
                                        ">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach

                                <button
                                    type="button"
                                    @click="toggleNotes('beneficiary-{{ $beneficiary->id }}')"
                                    class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                >
                                    ملاحظة
                                </button>
                            </div>
                        </div>

                        <div x-show="notesOpen['beneficiary-{{ $beneficiary->id }}']" x-transition class="mt-3">
                            <input
                                type="text"
                                name="attendance[{{ $beneficiary->id }}][notes]"
                                value="{{ $savedNotes }}"
                                class="form-input"
                                placeholder="أضف ملاحظة اختيارية"
                            >
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-slate-500">
                        لا يوجد مستفيدون لعرضهم.
                    </div>
                @endforelse
            </div>

            <div class="sticky bottom-4 mt-6">
                <button class="w-full rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#1e57a4]/20">
                    حفظ حضور المستفيدين
                </button>
            </div>
        </form>

        @if (auth()->user()->hasRole('admin'))
            <form
                method="POST"
                action="{{ route('attendance.employees.store') }}"
                class="rounded-[28px] bg-white p-5 shadow-soft"
                x-data="attendanceForm()"
            >
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                <div class="sticky top-3 z-10 mb-5 rounded-[24px] border border-slate-200 bg-white/95 p-4 backdrop-blur">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">حضور الموظفين</h2>
                                <p class="text-sm text-slate-500">{{ $attendanceDate }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="setAllEmployees('present')"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50"
                                >
                                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.294 6.543-6.544a1 1 0 011.415 0z" clip-rule="evenodd"/>
                                    </svg>
                                    تحديد الجميع حاضر
                                </button>

                                <button
                                    type="button"
                                    @click="setAllEmployees('absent')"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50"
                                >
                                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    تحديد الجميع غائب
                                </button>

                                <button
                                    type="button"
                                    @click="clearEmployees()"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-200"
                                >
                                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5.5 5A2.5 2.5 0 018 2.5h4A2.5 2.5 0 0114.5 5H17a1 1 0 110 2h-1v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 010-2h2.5zM8 4.5a.5.5 0 00-.5.5V5h5v-.5a.5.5 0 00-.5-.5H8z"/>
                                    </svg>
                                    مسح التحديد
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($employees->sortBy('full_name') as $employee)
                        @php
                            $saved = $employeeAttendance[$employee->id] ?? null;
                            $savedStatus = $saved->status ?? 'present';
                            $savedNotes = $saved->notes ?? '';
                        @endphp

                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition">
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-extrabold text-slate-900">
                                        {{ $employee->full_name }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($statuses as $key => $label)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="attendance[{{ $employee->id }}][status]"
                                                value="{{ $key }}"
                                                class="hidden peer employee-status"
                                                data-employee-id="{{ $employee->id }}"
                                                {{ $savedStatus === $key ? 'checked' : '' }}
                                            >
                                            <span class="inline-flex rounded-full border px-4 py-2 text-sm font-bold transition
                                                {{ $key === 'present' ? 'border-emerald-200 bg-white text-emerald-700 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white' : '' }}
                                                {{ $key === 'absent' ? 'border-rose-200 bg-white text-rose-700 peer-checked:border-rose-600 peer-checked:bg-rose-600 peer-checked:text-white' : '' }}
                                                {{ $key === 'late' ? 'border-amber-200 bg-white text-amber-700 peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white' : '' }}
                                                {{ $key === 'leave' ? 'border-slate-200 bg-white text-slate-700 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white' : '' }}
                                            ">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach

                                    <button
                                        type="button"
                                        @click="toggleNotes('employee-{{ $employee->id }}')"
                                        class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        ملاحظة
                                    </button>
                                </div>
                            </div>

                            <div x-show="notesOpen['employee-{{ $employee->id }}']" x-transition class="mt-3">
                                <input
                                    type="text"
                                    name="attendance[{{ $employee->id }}][notes]"
                                    value="{{ $savedNotes }}"
                                    class="form-input"
                                    placeholder="أضف ملاحظة اختيارية"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="sticky bottom-4 mt-6">
                    <button class="w-full rounded-2xl bg-[#e9b629] px-5 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-[#e9b629]/20">
                        حفظ حضور الموظفين
                    </button>
                </div>
            </form>
        @endif
    </section>

    <script>
        function attendanceForm() {
            return {
                notesOpen: {},

                toggleNotes(id) {
                    this.notesOpen[id] = !this.notesOpen[id];
                },

                setAll(status) {
                    this.$el.querySelectorAll('.beneficiary-status').forEach((input) => {
                        input.checked = input.value === status;
                    });
                },

                clearAll() {
                    this.$el.querySelectorAll('.beneficiary-status').forEach((input) => {
                        input.checked = false;
                    });
                },

                setAllEmployees(status) {
                    this.$el.querySelectorAll('.employee-status').forEach((input) => {
                        input.checked = input.value === status;
                    });
                },

                clearEmployees() {
                    this.$el.querySelectorAll('.employee-status').forEach((input) => {
                        input.checked = false;
                    });
                }
            }
        }
    </script>
</x-app-layout>