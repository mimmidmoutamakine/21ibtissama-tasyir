<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Models\DailyEvaluation;
use App\Models\MonthlyPlan;
use App\Models\WeeklyEvaluation;
use App\Models\WeeklyEvaluationEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyEvaluationController extends Controller
{
    use AuthorizesAssociationAccess;

    public function index(): View
    {
        $user = auth()->user();

        $plans = MonthlyPlan::query()
            ->with(['beneficiary', 'items'])
            ->when($user->hasRole('educator'), fn ($query) => $query->where('educator_id', $user->id))
            ->when($user->hasRole('specialist'), fn ($query) => $query->whereHas('beneficiary.serviceAssignments', fn ($assignments) => $assignments->where('specialist_id', $user->id)))
            ->latest('month_date')
            ->paginate(12);

        return view('weekly-evaluations.index', compact('plans'));
    }

    public function show(Request $request, MonthlyPlan $monthlyPlan): View
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        $selectedMonth = $request->filled('month')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->string('month')->toString())->startOfMonth()
            : $monthlyPlan->month_date->copy()->startOfMonth();

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

            $evaluation = WeeklyEvaluation::query()
                ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
                ->where('monthly_plan_id', $monthlyPlan->id)
                ->where('educator_id', $monthlyPlan->educator_id)
                ->whereDate('week_start', $monthStart->toDateString())
                ->whereDate('week_end', $monthEnd->toDateString())
                ->first();

        if (! $evaluation) {
            $evaluation = WeeklyEvaluation::create([
                'beneficiary_id' => $monthlyPlan->beneficiary_id,
                'monthly_plan_id' => $monthlyPlan->id,
                'educator_id' => $monthlyPlan->educator_id,
                'week_start' => $monthStart,
                'week_end' => $monthEnd,
            ]);
        }

        foreach ($monthlyPlan->items as $item) {
            WeeklyEvaluationEntry::firstOrCreate([
                'weekly_evaluation_id' => $evaluation->id,
                'monthly_plan_item_id' => $item->id,
            ]);
        }

        $evaluation->load([
            'beneficiary',
            'entries.monthlyPlanItem',
        ]);

        $dailyEvaluations = DailyEvaluation::query()
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->whereBetween('evaluation_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('entries.attempts', 'entries.dailyEvaluation')
            ->get();

        foreach ($evaluation->entries as $entry) {
            if ($entry->is_manually_edited) {
                continue;
            }

            $dailyEntriesForGoal = $dailyEvaluations
                ->flatMap->entries
                ->filter(fn ($dailyEntry) => $dailyEntry->monthly_plan_item_id === $entry->monthly_plan_item_id)
                ->values();

            $week1 = $this->calculateWeekFromDailyEntries($dailyEntriesForGoal, 1);
            $week2 = $this->calculateWeekFromDailyEntries($dailyEntriesForGoal, 2);
            $week3 = $this->calculateWeekFromDailyEntries($dailyEntriesForGoal, 3);
            $week4 = $this->calculateWeekFromDailyEntries($dailyEntriesForGoal, 4);

            $entry->update([
                'week_1_status_color' => $week1['status_color'],
                'week_1_progress_percent' => $week1['progress_percent'],
                'week_2_status_color' => $week2['status_color'],
                'week_2_progress_percent' => $week2['progress_percent'],
                'week_3_status_color' => $week3['status_color'],
                'week_3_progress_percent' => $week3['progress_percent'],
                'week_4_status_color' => $week4['status_color'],
                'week_4_progress_percent' => $week4['progress_percent'],
            ]);
        }

        $evaluation->refresh()->load([
            'beneficiary',
            'entries.monthlyPlanItem',
        ]);

        $groupedEntries = $evaluation->entries
            ->sortBy(fn ($entry) => $entry->monthlyPlanItem->id)
            ->groupBy(fn ($entry) => $entry->monthlyPlanItem->domain_axis ?? 'غير مصنف');

        return view('weekly-evaluations.show', [
            'selectedMonth' => $selectedMonth,
            'monthlyPlan' => $monthlyPlan,
            'evaluation' => $evaluation,
            'groupedEntries' => $groupedEntries,
        ]);
    }

    public function update(Request $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $selectedMonth = $request->filled('month')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->string('month')->toString())->startOfMonth()
            : $monthlyPlan->month_date->copy()->startOfMonth();

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

        $this->ensureMonthlyPlanAccess($monthlyPlan);
        abort_unless(auth()->user()->hasAnyRole(['educator', 'admin']), 403);

        $evaluationId = (int) $request->input('evaluation_id');

        $evaluation = WeeklyEvaluation::whereKey($evaluationId)
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->whereDate('week_start', $monthStart->toDateString())
            ->whereDate('week_end', $monthEnd->toDateString())
            ->firstOrFail();

        $states = $request->input('weeks', []);
        $notes = $request->input('entry_notes', []);

        $evaluation->load('entries');

        foreach ($evaluation->entries as $entry) {
            $week1 = $states[$entry->id][1] ?? null;
            $week2 = $states[$entry->id][2] ?? null;
            $week3 = $states[$entry->id][3] ?? null;
            $week4 = $states[$entry->id][4] ?? null;

            $entry->update([
                'week_1_status_color' => $week1 ?: null,
                'week_1_progress_percent' => $this->stateToPercent($week1),
                'week_2_status_color' => $week2 ?: null,
                'week_2_progress_percent' => $this->stateToPercent($week2),
                'week_3_status_color' => $week3 ?: null,
                'week_3_progress_percent' => $this->stateToPercent($week3),
                'week_4_status_color' => $week4 ?: null,
                'week_4_progress_percent' => $this->stateToPercent($week4),
                'note' => $notes[$entry->id] ?? null,
                'is_manually_edited' => true,
            ]);
        }

        activity()
            ->performedOn($evaluation)
            ->causedBy(auth()->user())
            ->event('weekly_evaluation_updated')
            ->log('تم تحديث التقييم الأسبوعي');

        return redirect()
            ->route('weekly-evaluations.show', [
                'monthlyPlan' => $monthlyPlan->id,
                'month' => $selectedMonth->format('Y-m'),
            ])
            ->with('status', 'تم حفظ التقييم الأسبوعي.');
    }

    protected function calculateWeekFromDailyEntries($dailyEntries, int $weekNumber): array
    {
        [$startDay, $endDay] = match ($weekNumber) {
            1 => [1, 7],
            2 => [8, 14],
            3 => [15, 21],
            4 => [22, 31],
        };

        $dailyPercents = $dailyEntries
            ->filter(function ($entry) use ($startDay, $endDay) {
                $day = optional(optional($entry->dailyEvaluation)->evaluation_date)->day;

                return $day
                    && $day >= $startDay
                    && $day <= $endDay;
            })
            ->map(function ($entry) {
                $attemptPercents = $entry->attempts
                    ->pluck('progress_percent')
                    ->filter(fn ($percent) => $percent > 0)
                    ->values();

                if ($attemptPercents->isEmpty()) {
                    return null;
                }

                return (int) round($attemptPercents->avg());
            })
            ->filter(fn ($percent) => ! is_null($percent))
            ->values();

        if ($dailyPercents->isEmpty()) {
            return [
                'status_color' => null,
                'progress_percent' => 0,
            ];
        }

        $average = (int) round($dailyPercents->avg());

        return [
            'status_color' => $this->percentToState($average),
            'progress_percent' => $average,
        ];
    }

    protected function stateToPercent(?string $state): int
    {
        return match ($state) {
            'red' => 25,
            'blue' => 50,
            'yellow' => 75,
            'green' => 100,
            default => 0,
        };
    }

    protected function percentToState(int $percent): ?string
    {
        return match (true) {
            $percent <= 0 => null,
            $percent <= 25 => 'red',
            $percent <= 50 => 'blue',
            $percent <= 75 => 'yellow',
            default => 'green',
        };
    }
}