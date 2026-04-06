<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">المشروع الفردي السنوي</h1>
    </x-slot>

    <div class="space-y-5" x-data="{ openBeneficiary: null }">
        @foreach ($beneficiaries as $beneficiary)
            <div class="rounded-[28px] bg-white p-6 shadow-soft">
                <button type="button" class="flex w-full items-center justify-between text-right" @click="openBeneficiary = openBeneficiary === {{ $beneficiary->id }} ? null : {{ $beneficiary->id }}">
                    <div>
                        <p class="text-xl font-extrabold text-slate-900">{{ $beneficiary->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $beneficiary->internal_code }}</p>
                    </div>
                    <span class="rounded-full bg-[#e9b629]/15 px-4 py-2 text-sm font-bold text-[#1e57a4]">{{ $beneficiary->annualProjects->count() }} مشروع</span>
                </button>
                <div x-show="openBeneficiary === {{ $beneficiary->id }}" class="mt-5 space-y-4">
                    @foreach ($beneficiary->annualProjects as $project)
                        <div class="rounded-3xl bg-slate-50 p-5">
                            <p class="font-bold text-slate-900">{{ $project->year_label }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $project->initial_situation_summary }}</p>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                @foreach ($project->domains as $domain)
                                    <div class="rounded-2xl bg-white p-4">
                                        <p class="font-bold text-[#1e57a4]">{{ $domain->name }}</p>
                                        <div class="mt-3 space-y-3">
                                            @foreach ($domain->objectives as $objective)
                                                <div class="rounded-2xl border border-slate-100 p-3">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <p class="text-sm font-semibold">{{ $objective->objective_text }}</p>
                                                        <span class="rounded-full bg-[#1e57a4]/10 px-3 py-1 text-xs font-bold text-[#1e57a4]">{{ $objective->progress_percent }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
