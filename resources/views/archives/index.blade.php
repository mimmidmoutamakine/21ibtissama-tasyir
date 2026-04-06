<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">الأرشيف والملفات</h1>
    </x-slot>

    <div class="rounded-[28px] bg-white p-6 shadow-soft">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">العنوان</th>
                        <th class="pb-3">الصنف</th>
                        <th class="pb-3">المرجع</th>
                        <th class="pb-3">تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($files as $file)
                        <tr>
                            <td class="py-4 font-semibold">{{ $file->title }}</td>
                            <td class="py-4">{{ $file->category }}</td>
                            <td class="py-4">{{ class_basename($file->archiveable_type) }}</td>
                            <td class="py-4">{{ $file->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $files->links() }}</div>
    </div>
</x-app-layout>
