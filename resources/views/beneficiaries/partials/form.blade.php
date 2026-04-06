<div x-data="{ tab: 'basic' }">
    <div class="sticky top-2 z-20 mb-4 rounded-[24px] bg-white/95 p-3 shadow-soft backdrop-blur">
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <button type="button"
                    @click="tab = 'basic'"
                    :class="tab === 'basic' ? 'bg-[#1e57a4] text-white' : 'border border-slate-200 text-slate-700'"
                    class="flex items-center justify-center gap-2 rounded-2xl px-3 py-3 text-sm font-bold transition">
                <span>👤</span>
                <span>الأساسية</span>
            </button>

            <button type="button"
                    @click="tab = 'family'"
                    :class="tab === 'family' ? 'bg-[#1e57a4] text-white' : 'border border-slate-200 text-slate-700'"
                    class="flex items-center justify-center gap-2 rounded-2xl px-3 py-3 text-sm font-bold transition">
                <span>👨‍👩‍👧</span>
                <span>الأسرة</span>
            </button>

            <button type="button"
                    @click="tab = 'benefit'"
                    :class="tab === 'benefit' ? 'bg-[#1e57a4] text-white' : 'border border-slate-200 text-slate-700'"
                    class="flex items-center justify-center gap-2 rounded-2xl px-3 py-3 text-sm font-bold transition">
                <span>📋</span>
                <span>الاستفادة</span>
            </button>

            <button type="button"
                    @click="tab = 'notes'"
                    :class="tab === 'notes' ? 'bg-[#1e57a4] text-white' : 'border border-slate-200 text-slate-700'"
                    class="flex items-center justify-center gap-2 rounded-2xl px-3 py-3 text-sm font-bold transition">
                <span>📝</span>
                <span>ملاحظات</span>
            </button>
        </div>

        <div class="mt-3 h-2 rounded-full bg-slate-100">
            <div class="h-2 rounded-full bg-[#1e57a4] transition-all duration-300"
                 :style="tab === 'basic' ? 'width:25%' : tab === 'family' ? 'width:50%' : tab === 'benefit' ? 'width:75%' : 'width:100%'">
            </div>
        </div>
    </div>

    <div x-show="tab === 'basic'" x-transition class="mobile-card">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="mobile-section-title">المعلومات الأساسية</h2>
            <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">1 / 4</span>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الكود الداخلي</label>
                <input type="text" name="internal_code" class="form-input" value="{{ old('internal_code', $beneficiary->internal_code) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الجنس</label>
                <select name="gender" class="form-input">
                    <option value="">-- اختر --</option>
                    <option value="male" @selected(old('gender', $beneficiary->gender) === 'male')>ذكر</option>
                    <option value="female" @selected(old('gender', $beneficiary->gender) === 'female')>أنثى</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الاسم الشخصي بالعربية</label>
                <input type="text" name="first_name_ar" class="form-input" value="{{ old('first_name_ar', $beneficiary->first_name_ar) }}" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الاسم العائلي بالعربية</label>
                <input type="text" name="last_name_ar" class="form-input" value="{{ old('last_name_ar', $beneficiary->last_name_ar) }}" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الاسم الشخصي بالفرنسية</label>
                <input type="text" name="first_name_fr" class="form-input" value="{{ old('first_name_fr', $beneficiary->first_name_fr) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الاسم العائلي بالفرنسية</label>
                <input type="text" name="last_name_fr" class="form-input" value="{{ old('last_name_fr', $beneficiary->last_name_fr) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">تاريخ الازدياد</label>
                <input type="date" name="date_of_birth" class="form-input" value="{{ old('date_of_birth', optional($beneficiary->date_of_birth)->format('Y-m-d')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">مكان الازدياد</label>
                <input type="text" name="place_of_birth" class="form-input" value="{{ old('place_of_birth', $beneficiary->place_of_birth) }}">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">العنوان</label>
                <textarea name="address" rows="3" class="form-input">{{ old('address', $beneficiary->address) }}</textarea>
            </div>
        </div>

        <div class="mt-5 flex justify-end">
            <button type="button" @click="tab = 'family'" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                التالي
            </button>
        </div>
    </div>

    <div x-show="tab === 'family'" x-transition class="mobile-card">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="mobile-section-title">الأسرة ووسائل التواصل</h2>
            <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">2 / 4</span>
        </div>

        @php
            $guardianDetails = old('guardian_details', $beneficiary->guardian_details ?? []);
        @endphp

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">اسم الأب</label>
                <input type="text" name="father_name" class="form-input" value="{{ old('father_name', data_get($guardianDetails, 'father_name')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">هاتف الأب</label>
                <input type="text" name="father_phone" class="form-input" value="{{ old('father_phone', data_get($guardianDetails, 'father_phone')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">اسم الأم</label>
                <input type="text" name="mother_name" class="form-input" value="{{ old('mother_name', data_get($guardianDetails, 'mother_name')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">هاتف الأم</label>
                <input type="text" name="mother_phone" class="form-input" value="{{ old('mother_phone', data_get($guardianDetails, 'mother_phone')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">اسم ولي الأمر</label>
                <input type="text" name="guardian_name" class="form-input" value="{{ old('guardian_name', data_get($guardianDetails, 'guardian_name')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">هاتف ولي الأمر</label>
                <input type="text" name="guardian_phone" class="form-input" value="{{ old('guardian_phone', data_get($guardianDetails, 'guardian_phone')) }}">
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between">
            <button type="button" @click="tab = 'basic'" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                السابق
            </button>
            <button type="button" @click="tab = 'benefit'" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                التالي
            </button>
        </div>
    </div>

    <div x-show="tab === 'benefit'" x-transition class="mobile-card">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="mobile-section-title">الاستفادة والتتبع</h2>
            <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">3 / 4</span>
        </div>

        @php
            $schooling = old('schooling_details', $beneficiary->schooling_details ?? []);
            $disability = old('disability_details', $beneficiary->disability_details ?? []);
        @endphp

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">تاريخ التسجيل</label>
                <input type="date" name="admission_date" class="form-input" value="{{ old('admission_date', optional($beneficiary->admission_date)->format('Y-m-d')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">نوع الاستفادة</label>
                <input type="text" name="benefit_type" class="form-input" value="{{ old('benefit_type', $beneficiary->benefit_type) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">البرنامج</label>
                <input type="text" name="program_name" class="form-input" value="{{ old('program_name', $beneficiary->program_name) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">الحالة</label>
                <input type="text" name="status" class="form-input" value="{{ old('status', $beneficiary->status ?? 'active') }}">
            </div>

            @if(auth()->user()->hasRole('admin'))
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">المؤطر الرئيسي</label>
                    <select name="primary_educator_id" class="form-input">
                        <option value="">غير معين</option>
                        @foreach($educators as $educator)
                            <option value="{{ $educator->id }}" @selected((string) old('primary_educator_id', $beneficiary->primary_educator_id) === (string) $educator->id)>
                                {{ $educator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mobile-check-row">
                    <input type="hidden" name="can_assigned_educator_edit" value="0">
                    <input type="checkbox" name="can_assigned_educator_edit" value="1" @checked(old('can_assigned_educator_edit', $beneficiary->can_assigned_educator_edit))>
                    <label class="text-sm font-bold text-slate-700">السماح للمؤطر المعيّن بالتعديل</label>
                </div>

                <div class="mobile-check-row">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $beneficiary->is_active ?? true))>
                    <label class="text-sm font-bold text-slate-700">المستفيد نشيط</label>
                </div>
            @endif

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">المستوى الدراسي</label>
                <input type="text" name="school_level" class="form-input" value="{{ old('school_level', data_get($schooling, 'level')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">المؤسسة الدراسية</label>
                <input type="text" name="school_institution" class="form-input" value="{{ old('school_institution', data_get($schooling, 'institution')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">نوع الإعاقة</label>
                <select name="disability_type" class="form-input">
                    <option value="">-- اختر --</option>
                    <option value="T21" @selected(old('disability_type', data_get($disability, 'type')) === 'T21')>T21</option>
                    <option value="TSA" @selected(old('disability_type', data_get($disability, 'type')) === 'TSA')>TSA</option>
                    <option value="IMC" @selected(old('disability_type', data_get($disability, 'type')) === 'IMC')>IMC</option>
                    <option value="اضطراب التعلم" @selected(old('disability_type', data_get($disability, 'type')) === 'اضطراب التعلم')>اضطراب التعلم</option>
                    <option value="إعاقة ذهنية" @selected(old('disability_type', data_get($disability, 'type')) === 'إعاقة ذهنية')>إعاقة ذهنية</option>
                    <option value="إعاقة سمعية" @selected(old('disability_type', data_get($disability, 'type')) === 'إعاقة سمعية')>إعاقة سمعية</option>
                    <option value="إعاقة بصرية" @selected(old('disability_type', data_get($disability, 'type')) === 'إعاقة بصرية')>إعاقة بصرية</option>
                    <option value="إعاقة حركية" @selected(old('disability_type', data_get($disability, 'type')) === 'إعاقة حركية')>إعاقة حركية</option>
                    <option value="أخرى" @selected(old('disability_type', data_get($disability, 'type')) === 'أخرى')>أخرى</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">درجة الإعاقة</label>
                <select name="disability_degree" class="form-input">
                    <option value="">-- اختر --</option>
                    <option value="خفيفة" @selected(old('disability_degree', data_get($disability, 'degree')) === 'خفيفة')>خفيفة</option>
                    <option value="متوسطة" @selected(old('disability_degree', data_get($disability, 'degree')) === 'متوسطة')>متوسطة</option>
                    <option value="شديدة" @selected(old('disability_degree', data_get($disability, 'degree')) === 'شديدة')>شديدة</option>
                </select>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between">
            <button type="button" @click="tab = 'family'" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                السابق
            </button>
            <button type="button" @click="tab = 'notes'" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                التالي
            </button>
        </div>
    </div>

    <div x-show="tab === 'notes'" x-transition class="mobile-card">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="mobile-section-title">ملاحظات</h2>
            <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">4 / 4</span>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-bold text-slate-700">ملاحظات إضافية</label>
            <textarea name="notes" rows="6" class="form-input">{{ old('notes', $beneficiary->notes) }}</textarea>
        </div>

        <div class="mt-5 flex items-center justify-between">
            <button type="button" @click="tab = 'benefit'" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                السابق
            </button>

            <div class="mobile-actions">
                <button type="submit" class="rounded-2xl bg-[#1e57a4] px-5 py-3 text-sm font-bold text-white">
                    حفظ
                </button>

                <a href="{{ route('beneficiaries.index') }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                    إلغاء
                </a>
            </div>
        </div>
    </div>
</div>