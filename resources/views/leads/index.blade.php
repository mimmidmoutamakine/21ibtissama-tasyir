<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">الإحالات والطلبات</h1>
    </x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.2fr,1fr]">
        <div class="rounded-[28px] bg-white p-6 shadow-soft">
            <h2 class="text-xl font-bold">قائمة الطلبات</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="text-slate-500">
                        <tr>
                            <th class="pb-3">الاسم</th>
                            <th class="pb-3">نوع الإعاقة</th>
                            <th class="pb-3">تاريخ التسجيل</th>
                            <th class="pb-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($leads as $lead)
                            <tr>
                                <td class="py-4 font-semibold text-slate-900">{{ $lead->fullName }}</td>
                                <td class="py-4">{{ $lead->disability_type ?? 'غير محدد' }}</td>
                                <td class="py-4">{{ optional($lead->registration_date)->format('Y-m-d') }}</td>
                                <td class="py-4"><x-status-badge :value="$lead->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $leads->links() }}</div>
        </div>

        <form method="POST" action="{{ route('leads.store') }}" class="rounded-[28px] bg-white p-6 shadow-soft">
            @csrf
            <h2 class="text-xl font-bold">إضافة طلب جديد</h2>
            <div class="mt-6 grid gap-6">
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="mb-4 text-sm font-bold text-[#1e57a4]">المرحلة 1: المعلومات الأساسية</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="first_name_ar" class="form-input" placeholder="الاسم الشخصي بالعربية">
                        <input name="last_name_ar" class="form-input" placeholder="الاسم العائلي بالعربية">
                        <input name="first_name_fr" class="form-input" placeholder="Prénom">
                        <input name="last_name_fr" class="form-input" placeholder="Nom">
                        <input type="date" name="date_of_birth" class="form-input">
                        <input name="place_of_birth" class="form-input" placeholder="مكان الازدياد">
                        <select name="gender" class="form-input">
                            <option value="">الجنس</option>
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                        <select name="disability_type" class="form-input">
                            <option value="">نوع الإعاقة</option>
                            @foreach ($disabilityTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <input name="father_phone" class="form-input" placeholder="هاتف الأب">
                        <input name="mother_phone" class="form-input" placeholder="هاتف الأم">
                    </div>
                </div>

                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="mb-4 text-sm font-bold text-[#1e57a4]">المرحلة 2: الوثائق والتتبع</p>
                    <div class="grid gap-3">
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"><input type="checkbox" name="document_checklist[]" value="شهادة الازدياد"> شهادة الازدياد</label>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"><input type="checkbox" name="document_checklist[]" value="بطاقة ولي الأمر"> بطاقة ولي الأمر</label>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"><input type="checkbox" name="document_checklist[]" value="تقرير طبي"> تقرير طبي</label>
                        <textarea name="notes" class="form-input min-h-28" placeholder="ملاحظات إضافية"></textarea>
                    </div>
                </div>
            </div>
            <button class="mt-6 w-full rounded-2xl bg-[#1e57a4] px-4 py-3 font-bold text-white">حفظ الطلب</button>
        </form>
    </section>
</x-app-layout>
