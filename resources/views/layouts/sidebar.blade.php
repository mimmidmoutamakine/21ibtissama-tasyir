@php
    $sections = [
        [
            'title' => 'الرئيسية',
            'items' => [
                ['label' => 'لوحة التحكم', 'route' => 'dashboard'],
            ],
        ],
        [
            'title' => 'الاستقبال والتسجيل',
            'items' => [
                ['label' => 'الإحالات والطلبات', 'route' => 'leads.index'],
                ['label' => 'المستفيدون', 'route' => 'beneficiaries.index'],
            ],
        ],
        [
            'title' => 'التتبع الفردي',
            'items' => [
                ['label' => 'المشروع الفردي السنوي', 'route' => 'annual-projects.index'],
                ['label' => 'الخطة الشهرية', 'route' => 'monthly-plans.index'],
                ['label' => 'التقييم اليومي', 'route' => 'daily-evaluations.index'],
                ['label' => 'التقييم الأسبوعي', 'route' => 'weekly-evaluations.index'],
                ['label' => 'التقييم الشهري', 'route' => 'monthly-evaluations.index'],
                ['label' => 'الحضور والمواظبة', 'route' => 'attendance.index'],
            ],
        ],
        [
            'title' => 'التسيير',
            'items' => [
                ['label' => 'الموارد البشرية', 'route' => 'employees.index', 'admin' => true],
                ['label' => 'المشاريع', 'route' => 'projects.index'],
                ['label' => 'المخزون', 'route' => 'stock.index'],
                ['label' => 'الأرشيف والملفات', 'route' => 'archives.index'],
            ],
        ],
        [
            'title' => 'الحساب',
            'items' => [
                ['label' => 'الإعدادات الشخصية', 'route' => 'profile.edit'],
            ],
        ],
    ];
@endphp

@php($isMobileSidebar = $mobile ?? false)

<aside @class([
    'sidebar-shell' => ! $isMobileSidebar,
    'w-full' => $isMobileSidebar,
])>
    <div class="rounded-[28px] bg-white p-6 shadow-soft">
        <div class="mb-8 flex items-center gap-3">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#1e57a4] text-2xl font-bold text-white">21</div>
            <div>
                <p class="text-lg font-bold text-slate-900">جمعية 21 ابتسامة</p>
                <p class="text-sm text-slate-500">منصة التسيير والمواكبة</p>
            </div>
        </div>

        <nav class="space-y-6">
            @foreach ($sections as $section)
                <div>
                    <p class="mb-3 px-2 text-xs font-extrabold tracking-wide text-slate-400">{{ $section['title'] }}</p>
                    <div class="space-y-2">
                        @foreach ($section['items'] as $item)
                            @continue(($item['admin'] ?? false) && ! auth()->user()->hasRole('admin'))
                            <a href="{{ route($item['route']) }}" @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs($item['route']),
                            ])
                            @if($isMobileSidebar)
                                @click="mobileMenuOpen = false"
                            @endif>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="mt-8 rounded-3xl bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
            <p class="text-sm text-slate-500">{{ auth()->user()->job_title ?? auth()->user()->getRoleNames()->implode(' / ') }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#1e57a4] hover:text-[#1e57a4]">
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </div>
</aside>
