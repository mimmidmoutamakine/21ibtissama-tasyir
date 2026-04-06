<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>التقرير الشهري</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 28px; color: #111827; }
        .page { border: 10px solid #1e57a4; padding: 30px 28px 40px; min-height: 92vh; position: relative; }
        h1 { font-size: 38px; text-align: center; margin: 24px 0 20px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 14px; }
        .section { margin-top: 26px; }
        .section-title { display: inline-block; border: 1px solid #111827; padding: 8px 18px; font-weight: bold; margin-bottom: 10px; }
        .box { border: 1px solid #cbd5e1; min-height: 120px; padding: 12px; line-height: 1.9; }
        .footer { position: absolute; bottom: 24px; left: 28px; right: 28px; text-align: center; font-size: 13px; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <div class="meta">
            <span>جمعية 21 ابتسامة للأطفال في وضعية إعاقة</span>
            <span>حرر بتاريخ: {{ now()->format('Y-m-d') }}</span>
        </div>
        <h1>التقرير الشهري</h1>
        <div class="meta">
            <span>الطفل(ة): {{ $beneficiary->full_name }}</span>
            <span>الفترة: {{ $monthLabel }}</span>
        </div>

        <div class="section">
            <div class="section-title">ملاحظات عامة</div>
            <div class="box">{{ $meeting?->general_notes }}</div>
        </div>

        <div class="section">
            <div class="section-title">الصعوبات والإكراهات</div>
            <div class="box">{{ $meeting?->difficulties }}</div>
        </div>

        <div class="section">
            <div class="section-title">التوصيات</div>
            <div class="box">{{ $meeting?->recommendations }}</div>
        </div>

        <div class="footer">
            <p>سيستمر الفريق التربوي خلال الشهر القادم في دعم مكتسبات الطفل والعمل على تجاوز الصعوبات المسجلة.</p>
            <p style="margin-top: 40px; font-weight: bold;">التوقيع</p>
        </div>
    </div>
</body>
</html>
