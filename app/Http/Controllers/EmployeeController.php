<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\EmployeeAttendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $employees = Employee::with(['documents', 'attendances'])->paginate(12);

        return view('employees.index', compact('employees'));
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate([
            'json_payload' => ['required', 'string'],
        ]);

        $payload = json_decode($request->string('json_payload')->toString(), true);

        if (! is_array($payload) || ! isset($payload['employees']) || ! is_array($payload['employees'])) {
            return back()->withErrors(['json_payload' => 'صيغة JSON غير صحيحة. يجب أن تحتوي على employees.'])->withInput();
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($payload, &$created, &$updated) {
            foreach ($payload['employees'] as $row) {
                $email = data_get($row, 'user.email');
                $employeeCode = data_get($row, 'employee.employee_code');

                if (! $email || ! $employeeCode) {
                    continue;
                }

                $user = User::firstOrNew(['email' => $email]);
                $isNewUser = ! $user->exists;

                $user->fill([
                    'name' => data_get($row, 'user.name'),
                    'job_title' => data_get($row, 'user.job_title'),
                    'phone' => data_get($row, 'user.phone'),
                    'is_active' => (bool) data_get($row, 'user.is_active', true),
                ]);

                if ($isNewUser) {
                    $user->password = Hash::make(data_get($row, 'user.password', 'password'));
                }

                $user->save();

                if ($role = $this->normalizeRole(data_get($row, 'user.role'))) {
                    $user->syncRoles([$role]);
                }

                $employee = Employee::firstOrNew(['employee_code' => $employeeCode]);
                $wasExisting = $employee->exists;

                $employee->fill([
                    'user_id' => $user->id,
                    'full_name' => data_get($row, 'employee.full_name', $user->name),
                    'role_label' => data_get($row, 'employee.role_label'),
                    'phone' => data_get($row, 'employee.phone', $user->phone),
                    'cin' => data_get($row, 'employee.cin'),
                    'hire_date' => data_get($row, 'employee.hire_date'),
                    'contract_type' => data_get($row, 'employee.contract_type'),
                    'qualification' => data_get($row, 'employee.qualification'),
                    'status' => data_get($row, 'employee.status', 'active'),
                    'document_requirements' => collect((array) data_get($row, 'documents', []))
                        ->map(fn ($document) => [
                            'category' => data_get($document, 'category'),
                            'title' => data_get($document, 'title'),
                            'required' => (bool) data_get($document, 'is_required', false),
                        ])->values()->all(),
                    'organization_notes' => data_get($row, 'employee.organization_notes', []),
                ]);
                $employee->save();

                $wasExisting ? $updated++ : $created++;

                foreach ((array) data_get($row, 'documents', []) as $document) {
                    EmployeeDocument::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'title' => data_get($document, 'title'),
                        ],
                        [
                            'category' => data_get($document, 'category', 'إداري'),
                            'is_required' => (bool) data_get($document, 'is_required', false),
                            'needs_update' => (bool) data_get($document, 'needs_update', false),
                            'file_path' => data_get($document, 'file_path'),
                        ]
                    );
                }

                foreach ((array) data_get($row, 'attendance_seed', []) as $attendance) {
                    EmployeeAttendance::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'attendance_date' => data_get($attendance, 'attendance_date'),
                        ],
                        [
                            'status' => data_get($attendance, 'status', 'present'),
                            'notes' => data_get($attendance, 'notes'),
                        ]
                    );
                }
            }
        });

        return back()->with('status', "تم استيراد {$created} موظف(ة) جديد(ة) وتحيين {$updated} ملف(ات).");
    }

    private function normalizeRole(?string $role): ?string
    {
        if (! $role) {
            return null;
        }

        $normalized = strtolower(trim($role));

        $aliases = [
            'admin' => 'admin',
            'administrator' => 'admin',
            'educator' => 'educator',
            'teacher' => 'educator',
            'staff' => null,
            'employee' => null,
            'specialist' => 'specialist',
            'therapist' => 'specialist',
            'psychologist' => 'specialist',
        ];

        $resolved = $aliases[$normalized] ?? null;

        if (! $resolved) {
            return null;
        }

        return Role::query()->where('name', $resolved)->where('guard_name', 'web')->exists()
            ? $resolved
            : null;
    }
}
