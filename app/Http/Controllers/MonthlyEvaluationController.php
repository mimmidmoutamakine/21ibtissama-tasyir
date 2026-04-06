<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Models\MonthlyEvaluation;
use App\Models\MonthlyEvaluationEntry;
use App\Models\MonthlyPlan;
use App\Models\WeeklyEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class MonthlyEvaluationController extends Controller
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

        return view('monthly-evaluations.index', compact('plans'));
    }

    public function show(Request $request, MonthlyPlan $monthlyPlan): View
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        $selectedMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->string('month')->toString())->startOfMonth()
            : $monthlyPlan->month_date->copy()->startOfMonth();

        $evaluation = $this->syncMonthlyEvaluation($monthlyPlan, $selectedMonth);
        $evaluation->load('entries.monthlyPlanItem', 'beneficiary', 'specialist');

        $groupedEntries = $evaluation->entries
            ->sortBy(fn ($entry) => $entry->monthlyPlanItem->id)
            ->groupBy(fn ($entry) => $entry->monthlyPlanItem->domain_axis ?? 'غير مصنف');

        return view('monthly-evaluations.show', [
            'monthlyPlan' => $monthlyPlan,
            'evaluation' => $evaluation,
            'selectedMonth' => $selectedMonth,
            'groupedEntries' => $groupedEntries,
        ]);
    }

    public function update(Request $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        $selectedMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->string('month')->toString())->startOfMonth()
            : $monthlyPlan->month_date->copy()->startOfMonth();

        $evaluationId = (int) $request->input('evaluation_id');

        $evaluation = MonthlyEvaluation::whereKey($evaluationId)
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->whereDate('month_date', $selectedMonth->toDateString())
            ->firstOrFail();

        $evaluation->load('entries');

        foreach ($evaluation->entries as $entry) {
            $payload = [
                'educator_progress_percent' => $entry->calculated_progress_percent,
                'educator_remarks' => $request->input("entries.{$entry->id}.educator_remarks"),
            ];

            if (auth()->user()->hasAnyRole(['specialist', 'admin'])) {
                $payload['specialist_final_decision'] = $request->input("entries.{$entry->id}.specialist_final_decision");
                $payload['specialist_remarks'] = $request->input("entries.{$entry->id}.specialist_remarks");
            }

            $entry->update($payload);
        }

        $evaluation->update([
            'general_remarks' => $request->string('general_remarks')->toString(),
            'specialist_id' => auth()->user()->hasAnyRole(['specialist', 'admin'])
                ? auth()->id()
                : $evaluation->specialist_id,
        ]);

        activity()
            ->performedOn($evaluation)
            ->causedBy(auth()->user())
            ->event('monthly_evaluation_updated')
            ->log('تم تحديث التقييم الشهري');

        return redirect()
            ->route('monthly-evaluations.show', [
                'monthlyPlan' => $monthlyPlan->id,
                'month' => $selectedMonth->format('Y-m'),
            ])
            ->with('status', 'تم حفظ التقييم الشهري.');
    }

    protected function syncMonthlyEvaluation(MonthlyPlan $monthlyPlan, Carbon $selectedMonth): MonthlyEvaluation
    {
        $evaluation = MonthlyEvaluation::firstOrCreate([
            'beneficiary_id' => $monthlyPlan->beneficiary_id,
            'monthly_plan_id' => $monthlyPlan->id,
            'educator_id' => $monthlyPlan->educator_id,
            'month_date' => $selectedMonth->copy()->startOfMonth(),
        ]);

        $weekStart = $selectedMonth->copy()->startOfMonth();
        $weekEnd = $selectedMonth->copy()->endOfMonth();

        $weeklyEvaluation = WeeklyEvaluation::query()
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->whereDate('week_start', $weekStart->toDateString())
            ->whereDate('week_end', $weekEnd->toDateString())
            ->with('entries')
            ->first();

        foreach ($monthlyPlan->items as $item) {
            $calculated = 0;

            if ($weeklyEvaluation) {
                $weeklyEntry = $weeklyEvaluation->entries
                    ->firstWhere('monthly_plan_item_id', $item->id);

                if ($weeklyEntry) {
                    $percents = collect([
                        $weeklyEntry->week_1_progress_percent,
                        $weeklyEntry->week_2_progress_percent,
                        $weeklyEntry->week_3_progress_percent,
                        $weeklyEntry->week_4_progress_percent,
                    ])->filter(fn ($value) => !is_null($value) && (int) $value > 0)->values();

                    $calculated = $percents->isNotEmpty()
                        ? (int) round($percents->avg())
                        : 0;
                }
            }

            MonthlyEvaluationEntry::updateOrCreate(
                [
                    'monthly_evaluation_id' => $evaluation->id,
                    'monthly_plan_item_id' => $item->id,
                ],
                [
                    'calculated_progress_percent' => $calculated,
                ],
            );
        }

        return $evaluation;
    }
}