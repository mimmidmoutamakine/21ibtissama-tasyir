@props(['value'])

@php
    $map = [
        'complete' => ['مكتمل', 'bg-emerald-100 text-emerald-700'],
        'almost_complete' => ['شبه مكتمل', 'bg-amber-100 text-amber-700'],
        'partially_complete' => ['مكتمل جزئيًا', 'bg-sky-100 text-sky-700'],
        'incomplete' => ['غير مكتمل', 'bg-rose-100 text-rose-700'],
        'approved' => ['مصادق عليه', 'bg-emerald-100 text-emerald-700'],
        'submitted' => ['تم الإرسال', 'bg-sky-100 text-sky-700'],
        'under_review' => ['قيد المراجعة', 'bg-amber-100 text-amber-700'],
        'rejected' => ['مرفوض', 'bg-rose-100 text-rose-700'],
        'revision' => ['مرجع للتعديل', 'bg-orange-100 text-orange-700'],
        'draft' => ['مسودة', 'bg-slate-100 text-slate-700'],
        'pending' => ['قيد الانتظار', 'bg-amber-100 text-amber-700'],
        'present' => ['حاضر', 'bg-emerald-100 text-emerald-700'],
        'late' => ['متأخر', 'bg-amber-100 text-amber-700'],
        'absent' => ['غائب', 'bg-rose-100 text-rose-700'],
        'leave' => ['رخصة', 'bg-slate-100 text-slate-700'],
    ];

    [$label, $class] = $map[$value] ?? [$value, 'bg-slate-100 text-slate-700'];
@endphp

<span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $class }}">{{ $label }}</span>
