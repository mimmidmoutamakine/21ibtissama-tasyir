<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">الموارد البشرية</h1>
                <p class="mt-1 text-sm text-slate-500">إدارة ملفات الموظفين والوثائق الأساسية.</p>
            </div>
            <button
                type="button"
                class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700"
                x-data
                @click="$dispatch('toggle-employee-import')"
            >
                استيراد جماعي JSON
            </button>
        </div>
    </x-slot>

    <div x-data="{ open: false }" @toggle-employee-import.window="open = !open" x-show="open" class="mb-6 rounded-[28px] bg-white p-6 shadow-soft">
        <form method="POST" action="{{ route('employees.import') }}" class="space-y-4">
            @csrf
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">استيراد جماعي للموارد البشرية</h2>
                    <p class="mt-1 text-sm text-slate-500">ألصق JSON الخاص بالموظفين وسيتم إنشاء المستخدمين والملفات والوثائق دفعة واحدة.</p>
                </div>
                <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700" @click="open = false">إغلاق</button>
            </div>
            <textarea name="json_payload" class="form-input min-h-[260px] font-mono text-xs" placeholder='{"employees":[...]}'>{{ old('json_payload') }}</textarea>
            <button class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">تنفيذ الاستيراد</button>
        </form>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($employees as $employee)
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold">{{ $employee->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $employee->employee_code }} - {{ $employee->role_label }}</p>
                    </div>
                    <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">{{ $employee->dossier_completion_percent }}%</span>
                </div>
                <div class="mt-4 space-y-2 text-sm text-slate-600">
                    <p>نوع العقد: {{ $employee->contract_type }}</p>
                    <p>المؤهل: {{ $employee->qualification }}</p>
                    <p>الوثائق: {{ $employee->documents->count() }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $employees->links() }}</div>
</x-app-layout>
