<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">المشاريع</h1>
    </x-slot>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($projects as $project)
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-lg font-bold">{{ $project->name }}</p>
                    <x-status-badge :value="$project->status" />
                </div>
                <p class="mt-3 text-sm text-slate-500">{{ $project->description }}</p>
                <div class="mt-4 text-sm text-slate-600">
                    <p>الرمز: {{ $project->code }}</p>
                    <p>الميزانية: {{ number_format((float) $project->budget, 2) }} درهم</p>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
