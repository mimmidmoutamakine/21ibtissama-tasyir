<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e57a4]">{{ $beneficiary->internal_code }}</p>
                <h1 class="text-3xl font-extrabold text-slate-900">تعديل بيانات المستفيد</h1>
            </div>

            <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">
                الرجوع للملف
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('beneficiaries.update', $beneficiary) }}">
        @csrf
        @method('PUT')
        @include('beneficiaries.partials.form')
    </form>

    @php
        $documents = $beneficiary->documents
            ->sortBy(fn ($doc) => array_search($doc->category, [
                'medical_file',
                'psychological_file',
                'educational_file',
                'individual_project',
                'mother_id_copy',
                'father_id_copy',
                'guardian_commitment',
            ]));

        $totalDocuments = $documents->count();
        $uploadedDocuments = $documents->whereNotNull('file_path')->count();
        $documentsProgress = $totalDocuments > 0 ? round(($uploadedDocuments / $totalDocuments) * 100) : 0;
    @endphp

    <div id="documents" class="mobile-card mt-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="mobile-section-title">وثائق مشروع تحسين ظروف التمدرس</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $uploadedDocuments }} من {{ $totalDocuments }} وثائق مرفوعة
                </p>
            </div>

            <div class="min-w-[140px]">
                <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-500">
                    <span>نسبة الاكتمال</span>
                    <span>{{ $documentsProgress }}%</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-[#1e57a4] transition-all duration-300"
                        style="width: {{ $documentsProgress }}%"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            @foreach ($documents as $document)
                <form method="POST"
                    action="{{ route('beneficiaries.documents.update', [$beneficiary, $document]) }}"
                    enctype="multipart/form-data"
                    x-data="{ openNotes: false }"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    @csrf

                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-extrabold text-slate-900">{{ $document->title }}</p>

                                @if($document->is_required)
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                        إلزامية
                                    </span>
                                @endif
                                @if($document->file_path)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                        مرفوعة
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                        غير مرفوعة
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1fr),180px]">
                                <label class="doc-upload-shell">
                                    <span class="doc-upload-label">
                                        {{ $document->file_path ? 'استبدال الملف' : 'رفع الملف' }}
                                    </span>
                                    <input type="file" name="document_file" class="doc-upload-input">
                                    <span class="doc-upload-name">
                                        {{ $document->file_path ? basename($document->file_path) : 'لم يتم اختيار ملف' }}
                                    </span>
                                </label>

                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button"
                                        @click="openNotes = !openNotes"
                                        class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">
                                    ملاحظات
                                </button>

                                @if($document->file_path)
                                    <a href="{{ asset('storage/' . $document->file_path) }}"
                                    target="_blank"
                                    class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700">
                                        فتح الملف
                                    </a>
                                @endif
                            </div>

                            <div x-show="openNotes" x-transition class="mt-3">
                                <textarea name="notes" rows="2" class="form-input">{{ $document->notes }}</textarea>
                            </div>
                        </div>

                        <div class="xl:w-auto">
                            <button type="submit"
                                    class="w-full rounded-2xl bg-[#1e57a4] px-4 py-2.5 text-sm font-bold text-white xl:w-auto">
                                حفظ
                            </button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
    
    <div class="mobile-card mt-6">
        <h2 class="mobile-section-title">تسجيل المغادرة</h2>
        <form method="POST" action="{{ route('beneficiaries.leave', $beneficiary) }}" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            @method('PATCH')

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">تاريخ المغادرة</label>
                <input type="date" name="exit_date" class="form-input" value="{{ old('exit_date', optional($beneficiary->exit_date)->format('Y-m-d')) }}">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">سبب المغادرة</label>
                <textarea name="left_reason" rows="4" class="form-input">{{ old('left_reason', $beneficiary->left_reason) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="rounded-2xl bg-rose-600 px-6 py-3 text-sm font-bold text-white">
                    تسجيل المغادرة
                </button>
            </div>
        </form>
    </div>
</x-app-layout>