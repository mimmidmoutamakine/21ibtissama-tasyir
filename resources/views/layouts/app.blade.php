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
    <body class="bg-slate-50 text-slate-800 antialiased">
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(30,87,164,0.12),_transparent_35%),radial-gradient(circle_at_bottom_left,_rgba(233,182,41,0.18),_transparent_30%)]"></div>
            <div class="relative mx-auto flex min-h-screen max-w-[1600px] gap-3 px-2 py-2 sm:px-3 sm:py-3 lg:gap-6 lg:px-6 lg:py-4">
                @include('layouts.sidebar')

                <main class="flex-1">
                    @isset($header)
                        <header class="mb-4 rounded-[24px] bg-white px-4 py-4 shadow-soft sm:px-5 sm:py-5 lg:mb-6 lg:rounded-[28px] lg:px-6 lg:py-5">
                            {{ $header }}
                        </header>
                    @endisset

                    @if (session('status'))
                        <div class="mb-6 rounded-3xl border border-[#1e57a4]/15 bg-[#1e57a4]/5 px-5 py-4 text-sm font-semibold text-[#1e57a4]">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="space-y-6">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
