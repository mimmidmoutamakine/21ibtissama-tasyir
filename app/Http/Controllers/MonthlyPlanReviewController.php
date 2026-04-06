<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Http\Requests\StoreMonthlyPlanReviewRequest;
use App\Models\MonthlyPlan;
use App\Models\MonthlyPlanReview;
use Illuminate\Http\RedirectResponse;

class MonthlyPlanReviewController extends Controller
{
    use AuthorizesAssociationAccess;

    public function store(StoreMonthlyPlanReviewRequest $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        abort_unless(auth()->user()->hasAnyRole(['specialist', 'admin']), 403);

        $validated = $request->validated();

        MonthlyPlanReview::create([
            'monthly_plan_id' => $monthlyPlan->id,
            'reviewer_id' => auth()->id(),
            'decision' => $validated['decision'],
            'remarks' => $validated['remarks'] ?? null,
            'changes_summary' => $validated['changes_summary'] ?? null,
            'reviewed_at' => now(),
        ]);

        $monthlyPlan->update([
            'status' => $validated['decision'],
            'approved_at' => $validated['decision'] === 'approved' ? now() : null,
            'revision_summary' => $validated['changes_summary'] ?? null,
        ]);

        activity()
            ->performedOn($monthlyPlan)
            ->causedBy(auth()->user())
            ->event('monthly_plan_reviewed')
            ->log('تمت مراجعة الخطة الشهرية');

        return redirect()
            ->route('monthly-plans.show', $monthlyPlan)
            ->with('status', 'تم حفظ قرار المراجعة بنجاح.');
    }
}