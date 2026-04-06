<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFile;
use App\Models\AssociationProject;
use App\Models\Beneficiary;
use App\Models\EmployeeAttendance;
use App\Models\Lead;
use App\Models\MonthlyPlan;
use App\Models\StockItem;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $beneficiaryScope = Beneficiary::query()
            ->when($user->hasRole('educator'), fn ($query) => $query->where('primary_educator_id', $user->id))
            ->when($user->hasRole('specialist'), fn ($query) => $query->whereHas('serviceAssignments', fn ($assignments) => $assignments->where('specialist_id', $user->id)));

        $data = [
            'beneficiariesCount' => (clone $beneficiaryScope)->count(),
            'newLeads' => Lead::where('status', 'new')->count(),
            'incompleteDossiers' => Beneficiary::all()->filter(fn (Beneficiary $beneficiary) => $beneficiary->dossier_completion_percent < 100)->count(),
            'pendingPlans' => MonthlyPlan::whereIn('status', ['submitted', 'under_review'])->count(),
            'rejectedPlans' => MonthlyPlan::whereIn('status', ['rejected', 'revision'])->count(),
            'overdueEvaluations' => MonthlyPlan::whereMonth('month_date', now()->month)->whereDoesntHave('monthlyEvaluations')->count(),
            'upcomingReviews' => Beneficiary::whereDoesntHave('periodicMeetings', fn ($query) => $query->whereMonth('meeting_month', now()->month))->count(),
            'employeeAttendanceSummary' => EmployeeAttendance::whereDate('attendance_date', today())->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status'),
            'activeProjects' => AssociationProject::where('status', 'active')->count(),
            'stockAlerts' => StockItem::all()->filter->is_alert->count(),
            'recentUploads' => ArchiveFile::latest()->take(5)->get(),
            'recentActivity' => Activity::query()->latest()->take(8)->get(),
            'monthlyPlans' => MonthlyPlan::with('beneficiary')->latest('month_date')->take(6)->get(),
            'beneficiaries' => $beneficiaryScope->with('primaryEducator')->take(6)->get(),
        ];

        return view('dashboard.index', compact('data'));
    }
}
