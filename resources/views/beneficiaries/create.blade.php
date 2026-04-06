<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-[#1e57a4]">المستفيدون</p>
            <h1 class="text-3xl font-extrabold text-slate-900">إضافة مستفيد جديد</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('beneficiaries.store') }}">
        @csrf
        @include('beneficiaries.partials.form')
    </form>
</x-app-layout>