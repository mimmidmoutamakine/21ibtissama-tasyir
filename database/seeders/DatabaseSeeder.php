<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach (['admin', 'educator', 'specialist'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $credentials = [];

        $adminAccounts = [
            [
                'name' => 'محمد عطاوي',
                'email' => 'admin1@21ibtissama.ma',
                'password' => 'Admin21@2026',
                'job_title' => 'مدير عام',
                'phone' => null,
                'employee_code' => 'ADM-001',
                'role_label' => 'الإدارة',
            ],
            [
                'name' => 'إدارة 21 ابتسامة',
                'email' => 'admin2@21ibtissama.ma',
                'password' => 'Admin21@2026B',
                'job_title' => 'إدارة التطبيق',
                'phone' => null,
                'employee_code' => 'ADM-002',
                'role_label' => 'الإدارة',
            ],
        ];

        foreach ($adminAccounts as $adminData) {
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'job_title' => $adminData['job_title'],
                    'phone' => $adminData['phone'],
                    'is_active' => true,
                    'password' => Hash::make($adminData['password']),
                ]
            );

            $user->syncRoles(['admin']);

            Employee::updateOrCreate(
                ['employee_code' => $adminData['employee_code']],
                [
                    'user_id' => $user->id,
                    'full_name' => $adminData['name'],
                    'role_label' => $adminData['role_label'],
                    'phone' => $adminData['phone'],
                    'cin' => null,
                    'hire_date' => now()->toDateString(),
                    'contract_type' => 'CDI',
                    'qualification' => null,
                    'status' => 'active',
                    'document_requirements' => config('association.employee_required_documents'),
                    'organization_notes' => ['department' => 'الإدارة'],
                ]
            );

            $credentials[] = [
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => $adminData['password'],
                'role' => 'admin',
            ];
        }

        $employeesPayload = json_decode(File::get(database_path('data/app_employees.json')), true);

        foreach ((array) data_get($employeesPayload, 'employees', []) as $row) {
            $email = data_get($row, 'user.email');
            $employeeCode = data_get($row, 'employee.employee_code');

            if (! $email || ! $employeeCode) {
                continue;
            }

            $temporaryPassword = $this->temporaryPassword($employeeCode);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => data_get($row, 'user.name'),
                    'job_title' => data_get($row, 'user.job_title'),
                    'phone' => data_get($row, 'user.phone'),
                    'is_active' => (bool) data_get($row, 'user.is_active', true),
                    'password' => Hash::make($temporaryPassword),
                ]
            );

            if ($role = $this->normalizeRole(data_get($row, 'user.role'))) {
                $user->syncRoles([$role]);
            } else {
                $user->syncRoles([]);
            }

            $employee = Employee::updateOrCreate(
                ['employee_code' => $employeeCode],
                [
                    'user_id' => $user->id,
                    'full_name' => data_get($row, 'employee.full_name', data_get($row, 'user.name')),
                    'role_label' => data_get($row, 'employee.role_label'),
                    'phone' => data_get($row, 'employee.phone', data_get($row, 'user.phone')),
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
                ]
            );

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

            $credentials[] = [
                'name' => $user->name,
                'email' => $email,
                'password' => $temporaryPassword,
                'role' => $role ?? 'none',
                'employee_code' => $employeeCode,
            ];
        }

        $this->writeCredentialsFile($credentials);
    }

    private function normalizeRole(?string $role): ?string
    {
        if (! $role) {
            return null;
        }

        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'admin', 'administrator' => 'admin',
            'educator', 'teacher' => 'educator',
            'specialist', 'therapist', 'psychologist' => 'specialist',
            default => null,
        };
    }

    private function temporaryPassword(string $employeeCode): string
    {
        $suffix = str_replace(['EMP-', 'ADM-', '-'], '', strtoupper($employeeCode));

        return 'Init21@'.$suffix;
    }

    private function writeCredentialsFile(array $credentials): void
    {
        $directory = storage_path('app/private');

        File::ensureDirectoryExists($directory);
        File::put(
            $directory.'/initial_credentials.json',
            json_encode(['generated_at' => now()->toDateTimeString(), 'accounts' => $credentials], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
