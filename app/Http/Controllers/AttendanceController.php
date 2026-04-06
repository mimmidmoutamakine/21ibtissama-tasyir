<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\BeneficiaryAttendance;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $attendanceDate = $request->string('date')->toString() ?: now()->toDateString();

        $beneficiaries = Beneficiary::query()
            ->when($user->hasRole('educator'), fn ($query) => $query->where('primary_educator_id', $user->id))
            ->when($user->hasRole('specialist'), fn ($query) => $query->whereHas('serviceAssignments', fn ($assignments) => $assignments->where('specialist_id', $user->id)))
            ->get();

        $beneficiaryAttendance = BeneficiaryAttendance::query()
            ->whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('beneficiary_id');

        $employeeAttendance = EmployeeAttendance::query()
            ->whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('employee_id');

        return view('attendance.index', [
            'beneficiaries' => $beneficiaries,
            'employees' => Employee::with('documents')->get(),
            'statuses' => config('association.attendance_statuses'),
            'attendanceDate' => $attendanceDate,
            'beneficiaryAttendance' => $beneficiaryAttendance,
            'employeeAttendance' => $employeeAttendance,
        ]);
    }

    public function storeBeneficiaryAttendance(Request $request): RedirectResponse
    {
        $attendanceDate = $request->input('attendance_date', now()->toDateString());

        foreach ($request->input('attendance', []) as $beneficiaryId => $payload) {
            BeneficiaryAttendance::updateOrCreate(
                [
                    'beneficiary_id' => $beneficiaryId,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'educator_id' => auth()->id(),
                    'status' => $payload['status'] ?? 'present',
                    'notes' => $payload['notes'] ?? null,
                ],
            );
        }

        return back()->with('status', 'تم تحيين حضور المستفيدين.');
    }

    public function storeEmployeeAttendance(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $attendanceDate = $request->input('attendance_date', now()->toDateString());

        foreach ($request->input('attendance', []) as $employeeId => $payload) {
            EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status' => $payload['status'] ?? 'present',
                    'notes' => $payload['notes'] ?? null,
                ],
            );
        }

        return back()->with('status', 'تم تحيين حضور الموظفين.');
    }
}