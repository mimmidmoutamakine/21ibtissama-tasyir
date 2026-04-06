<?php

return [
    'beneficiary_required_documents' => [
        ['section' => 'إداري', 'title' => 'استمارة المعلومات الأساسية', 'required' => true],
        ['section' => 'إداري', 'title' => 'نسخة من بطاقة ولي الأمر', 'required' => true],
        ['section' => 'إداري', 'title' => 'عقد الالتزام والاستفادة', 'required' => true],
        ['section' => 'طبي', 'title' => 'تقرير طبي حديث', 'required' => true],
        ['section' => 'نفسي', 'title' => 'تقييم نفسي', 'required' => false],
        ['section' => 'تربوي', 'title' => 'تقييم تربوي بيداغوجي', 'required' => true],
        ['section' => 'حضور وتمدرس', 'title' => 'شهادة التأمين المدرسي', 'required' => false],
    ],
    'employee_required_documents' => [
        ['category' => 'إداري', 'title' => 'نسخة بطاقة التعريف', 'required' => true],
        ['category' => 'تعاقدي', 'title' => 'عقد العمل', 'required' => true],
        ['category' => 'كفاءات', 'title' => 'الشهادات والمؤهلات', 'required' => true],
        ['category' => 'حماية', 'title' => 'تأمين/تصريح CNSS', 'required' => false],
    ],
    'services' => [
        'الدعم والمواكبة النفسية',
        'Psychomotricité',
        'Kiné',
        'Orthophonie',
        'Ergothérapie',
        'التربية الخاصة',
    ],
    'monthly_plan_statuses' => [
        'draft' => 'مسودة',
        'editable' => 'قابل للتعديل',
        'submitted' => 'تم الإرسال',
        'under_review' => 'قيد المراجعة',
        'approved' => 'مصادق عليه',
        'rejected' => 'مرفوض',
        'revision' => 'مرجع للتعديل',
        'closed' => 'مغلق',
    ],
    'attendance_statuses' => [
        'present' => 'حاضر',
        'late' => 'متأخر',
        'absent' => 'غائب',
        'leave' => 'رخصة',
    ],
    'specialist_decisions' => [
        'continue' => 'الاستمرار بنفس الهدف',
        'bigger' => 'استبداله بهدف أكبر',
        'smaller' => 'استبداله بهدف أبسط',
    ],
    'monthly_final_decisions' => [
        'achieved' => 'منجَز',
        'continue' => 'استمر',
        'modify' => 'تحيين',
    ],
];
