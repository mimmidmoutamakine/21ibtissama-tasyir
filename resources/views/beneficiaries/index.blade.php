<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">ملفات المستفيدين</h1>
                <p class="mt-1 text-sm text-slate-500">إدارة وتتبع ملفات المستفيدين داخل الجمعية.</p>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700"
                        x-data
                        @click="$dispatch('toggle-beneficiary-import')"
                    >
                        استيراد جماعي JSON
                    </button>
                    <a href="{{ route('beneficiaries.create') }}" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                        إضافة مستفيد
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    @if (auth()->user()->hasRole('admin'))
        <div x-data="{ open: false }" @toggle-beneficiary-import.window="open = !open" x-show="open" class="rounded-[28px] bg-white p-6 shadow-soft">
            <form method="POST" action="{{ route('beneficiaries.import') }}" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">استيراد جماعي للمستفيدين</h2>
                        <p class="mt-1 text-sm text-slate-500">ألصق JSON بنفس القالب المتفق عليه وسيتم الإنشاء أو التحيين تلقائيًا.</p>
                    </div>
                    <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700" @click="open = false">إغلاق</button>
                </div>
                <textarea name="json_payload" class="form-input min-h-[260px] font-mono text-xs" placeholder='{"beneficiaries":[...]}'>{{ old('json_payload') }}</textarea>
                <button class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">تنفيذ الاستيراد</button>
            </form>
        </div>
    @endif

    <div class="rounded-[28px] bg-white p-4 shadow-soft">
        <form method="GET" class="flex flex-wrap gap-3">
            <a href="{{ route('beneficiaries.index') }}"
               class="rounded-2xl px-4 py-2 text-sm font-bold {{ request('status_filter') ? 'border border-slate-200 text-slate-700' : 'bg-[#1e57a4] text-white' }}">
                الكل
            </a>

            <button name="status_filter" value="active" class="rounded-2xl px-4 py-2 text-sm font-bold {{ request('status_filter') === 'active' ? 'bg-emerald-600 text-white' : 'border border-slate-200 text-slate-700' }}">
                النشطون
            </button>

            <button name="status_filter" value="left" class="rounded-2xl px-4 py-2 text-sm font-bold {{ request('status_filter') === 'left' ? 'bg-rose-600 text-white' : 'border border-slate-200 text-slate-700' }}">
                المغادرون
            </button>
        </form>
    </div>

    <section class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
        @foreach ($beneficiaries as $beneficiary)
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xl font-extrabold text-slate-900">{{ $beneficiary->full_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $beneficiary->internal_code ?: 'بدون كود' }}</p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <x-status-badge :value="$beneficiary->dossier_status" />
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $beneficiary->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $beneficiary->is_active ? 'نشيط' : 'غادر' }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid gap-2 text-sm text-slate-600">
                    <p>المؤطر الرئيسي: {{ $beneficiary->primaryEducator?->name ?? 'غير معين' }}</p>
                    <p>نوع الاستفادة: {{ $beneficiary->benefit_type ?? 'غير محدد' }}</p>
                    <p>الخدمات: {{ $beneficiary->serviceAssignments->pluck('service.name')->filter()->implode('، ') ?: 'لا توجد' }}</p>
                </div>

                <div class="mt-5 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-[#e9b629]" style="width: {{ $beneficiary->dossier_completion_percent }}%"></div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="quick-action">عرض</a>

                    @php
                        $canEdit = auth()->user()->hasRole('admin')
                            || (auth()->user()->hasRole('educator')
                                && (int) $beneficiary->primary_educator_id === (int) auth()->id()
                                && $beneficiary->can_assigned_educator_edit);
                    @endphp

                    @if ($canEdit)
                        <a href="{{ route('beneficiaries.edit', $beneficiary) }}" class="quick-action">تعديل</a>
                    @endif

                    @if ($beneficiary->whatsapp_father_url)
                        <a href="{{ $beneficiary->whatsapp_father_url }}" target="_blank" class="quick-action">واتساب الأب</a>
                    @endif

                    @if ($beneficiary->whatsapp_mother_url)
                        <a href="{{ $beneficiary->whatsapp_mother_url }}" target="_blank" class="quick-action">واتساب الأم</a>
                    @endif

                    @if ($beneficiary->whatsapp_guardian_url)
                        <a href="{{ $beneficiary->whatsapp_guardian_url }}" target="_blank" class="quick-action">واتساب الولي</a>
                    @endif
                </div>
            </div>
        @endforeach
    </section>

    <div class="mt-6">{{ $beneficiaries->links() }}</div>
</x-app-layout>
