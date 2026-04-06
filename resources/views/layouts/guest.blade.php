<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '21 Ibtissama') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md rounded-[32px] bg-white px-8 py-8 shadow-soft">
                <div class="mb-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#1e57a4] text-2xl font-extrabold text-white">21</div>
                    <h1 class="mt-4 text-2xl font-extrabold text-slate-900">جمعية 21 ابتسامة</h1>
                    <p class="mt-2 text-sm text-slate-500">نظام التتبع الفردي والتسيير الداخلي</p>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
