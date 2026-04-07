<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AnnualProjectController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DailyEvaluationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MonthlyEvaluationController;
use App\Http\Controllers\MonthlyPlanController;
use App\Http\Controllers\MonthlyPlanReviewController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WeeklyEvaluationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

    Route::get('/beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    Route::post('/beneficiaries/import', [BeneficiaryController::class, 'import'])->name('beneficiaries.import');
    Route::get('/beneficiaries/create', [BeneficiaryController::class, 'create'])->name('beneficiaries.create');
    Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');
    Route::get('/beneficiaries/{beneficiary}/edit', [BeneficiaryController::class, 'edit'])->name('beneficiaries.edit');
    Route::put('/beneficiaries/{beneficiary}', [BeneficiaryController::class, 'update'])->name('beneficiaries.update');
    Route::patch('/beneficiaries/{beneficiary}/leave', [BeneficiaryController::class, 'leave'])->name('beneficiaries.leave');
    Route::get('/beneficiaries/{beneficiary}', [BeneficiaryController::class, 'show'])->name('beneficiaries.show');
    Route::post('/beneficiaries/{beneficiary}/documents/{document}', [BeneficiaryController::class, 'updateDocument'])->name('beneficiaries.documents.update');
    Route::post('/beneficiaries/{beneficiary}/documents/{document}', [BeneficiaryController::class, 'updateDocument'])->name('beneficiaries.documents.update');

    Route::get('/annual-projects', [BeneficiaryController::class, 'annualProjects'])->name('annual-projects.index');
    Route::get('/annual-projects/{annualProject}', [AnnualProjectController::class, 'show'])->name('annual-projects.show');
    Route::get('/annual-projects/{annualProject}/edit', [AnnualProjectController::class, 'edit'])->name('annual-projects.edit');
    Route::put('/annual-projects/{annualProject}', [AnnualProjectController::class, 'update'])->name('annual-projects.update');
    Route::get('/beneficiaries/{beneficiary}/annual-projects/create', [AnnualProjectController::class, 'create'])->name('annual-projects.create');
    Route::post('/beneficiaries/{beneficiary}/annual-projects', [AnnualProjectController::class, 'store'])->name('annual-projects.store');

    Route::get('/monthly-plans', [MonthlyPlanController::class, 'index'])->name('monthly-plans.index');
    Route::get('/monthly-plans/{monthlyPlan}', [MonthlyPlanController::class, 'show'])->name('monthly-plans.show');
    Route::get('/monthly-plans/{monthlyPlan}/edit', [MonthlyPlanController::class, 'edit'])->name('monthly-plans.edit');
    Route::put('/monthly-plans/{monthlyPlan}', [MonthlyPlanController::class, 'update'])->name('monthly-plans.update');
    Route::post('/monthly-plans/{monthlyPlan}/submit', [MonthlyPlanController::class, 'submit'])->name('monthly-plans.submit');

    Route::post('/monthly-plans/{monthlyPlan}/review', [MonthlyPlanReviewController::class, 'store'])->name('monthly-plans.review');

    Route::get('/daily-evaluations/{monthlyPlan}', [DailyEvaluationController::class, 'show'])->name('daily-evaluations.show');
    Route::post('/daily-evaluations/{monthlyPlan}', [DailyEvaluationController::class, 'update'])->name('daily-evaluations.update');
    Route::get('/daily-evaluations', [DailyEvaluationController::class, 'index'])->name('daily-evaluations.index');

    Route::get('/weekly-evaluations/{monthlyPlan}', [WeeklyEvaluationController::class, 'show'])->name('weekly-evaluations.show');
    Route::post('/weekly-evaluations/{monthlyPlan}', [WeeklyEvaluationController::class, 'update'])->name('weekly-evaluations.update');
    Route::get('/weekly-evaluations', [WeeklyEvaluationController::class, 'index'])->name('weekly-evaluations.index');

    Route::get('/monthly-evaluations/{monthlyPlan}', [MonthlyEvaluationController::class, 'show'])->name('monthly-evaluations.show');
    Route::post('/monthly-evaluations/{monthlyPlan}', [MonthlyEvaluationController::class, 'update'])->name('monthly-evaluations.update');
    Route::get('/monthly-evaluations', [MonthlyEvaluationController::class, 'index'])->name('monthly-evaluations.index');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/beneficiaries', [AttendanceController::class, 'storeBeneficiaryAttendance'])->name('attendance.beneficiaries.store');
    Route::post('/attendance/employees', [AttendanceController::class, 'storeEmployeeAttendance'])->name('attendance.employees.store');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
    Route::get('/reports/monthly/{beneficiary}', [ReportController::class, 'monthly'])->name('reports.monthly');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
