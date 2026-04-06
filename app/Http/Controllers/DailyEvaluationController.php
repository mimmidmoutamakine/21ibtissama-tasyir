<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Models\DailyEvaluation;
use App\Models\DailyEvaluationAttempt;
use App\Models\DailyEvaluationEntry;
use App\Models\MonthlyPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DailyEvaluationController extends Controller
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

        return view('daily-evaluations.index', compact('plans'));
    }

    public function show(Request $request, MonthlyPlan $monthlyPlan): View
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        $selectedDate = $request->filled('date')
            ? \Carbon\Carbon::parse($request->string('date')->toString())
            : today();

        $evaluation = DailyEvaluation::query()
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->whereDate('evaluation_date', $selectedDate->toDateString())
            ->first();

        if (! $evaluation) {
            $evaluation = DailyEvaluation::create([
                'beneficiary_id' => $monthlyPlan->beneficiary_id,
                'monthly_plan_id' => $monthlyPlan->id,
                'educator_id' => $monthlyPlan->educator_id,
                'evaluation_date' => $selectedDate->copy()->startOfDay(),
                'general_notes' => null,
            ]);
        }

        foreach ($monthlyPlan->items as $item) {
            $entry = DailyEvaluationEntry::firstOrCreate([
                'daily_evaluation_id' => $evaluation->id,
                'monthly_plan_item_id' => $item->id,
            ]);

            for ($i = 1; $i <= 5; $i++) {
                \App\Models\DailyEvaluationAttempt::firstOrCreate([
                    'daily_evaluation_entry_id' => $entry->id,
                    'attempt_number' => $i,
                ], [
                    'status_color' => null,
                    'progress_percent' => 0,
                ]);
            }
        }

        $evaluation->load([
            'beneficiary',
            'entries.monthlyPlanItem',
            'entries.attempts',
        ]);

        $groupedEntries = $evaluation->entries
            ->sortBy(fn ($entry) => $entry->monthlyPlanItem->id)
            ->groupBy(fn ($entry) => $entry->monthlyPlanItem->domain_axis ?? 'غير مصنف');

        return view('daily-evaluations.show', [
            'monthlyPlan' => $monthlyPlan,
            'evaluation' => $evaluation,
            'groupedEntries' => $groupedEntries,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function update(Request $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);
        abort_unless(auth()->user()->hasAnyRole(['educator', 'admin']), 403);

        $selectedDate = $request->filled('date')
            ? \Carbon\Carbon::parse($request->string('date')->toString())
            : today();

        $evaluationId = (int) $request->input('evaluation_id');

        $evaluation = DailyEvaluation::whereKey($evaluationId)
            ->where('beneficiary_id', $monthlyPlan->beneficiary_id)
            ->where('monthly_plan_id', $monthlyPlan->id)
            ->where('educator_id', $monthlyPlan->educator_id)
            ->firstOrFail();

        $attemptStates = $request->input('attempts', []);
        $entryNotes = $request->input('entry_notes', []);

        $evaluation->load('entries.attempts');

        foreach ($evaluation->entries as $entry) {
            foreach ($entry->attempts as $attempt) {
                $state = $attemptStates[$entry->id][$attempt->attempt_number] ?? null;

                $attempt->update([
                    'status_color' => $state ?: null,
                    'progress_percent' => $this->stateToPercent($state),
                ]);
            }

            $entry->update([
                'note' => $entryNotes[$entry->id] ?? null,
            ]);
        }

        activity()
            ->performedOn($evaluation)
            ->causedBy(auth()->user())
            ->event('daily_evaluation_updated')
            ->log('تم تحديث التقييم اليومي');

        return redirect()
            ->route('daily-evaluations.show', [
                'monthlyPlan' => $monthlyPlan->id,
                'date' => $selectedDate->toDateString(),
            ])
            ->with('status', 'تم حفظ التقييم اليومي.');
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
}