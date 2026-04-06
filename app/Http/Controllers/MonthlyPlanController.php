<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Http\Requests\UpdateMonthlyPlanRequest;
use App\Models\MonthlyPlan;
use App\Models\MonthlyPlanItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonthlyPlanController extends Controller
{
    use AuthorizesAssociationAccess;

    public function index(): View
    {
        $user = auth()->user();

        $plans = MonthlyPlan::query()
            ->with(['beneficiary', 'educator', 'reviews.reviewer'])
            ->when(
                $user->hasRole('educator'),
                fn ($query) => $query->where('educator_id', $user->id)
            )
            ->when(
                $user->hasRole('specialist'),
                fn ($query) => $query->whereHas(
                    'beneficiary.serviceAssignments',
                    fn ($assignments) => $assignments->where('specialist_id', $user->id)
                )
            )
            ->latest('month_date')
            ->paginate(10);

        return view('monthly-plans.index', compact('plans'));
    }

    public function show(MonthlyPlan $monthlyPlan): View
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        $monthlyPlan->load([
            'beneficiary',
            'educator',
            'items.annualObjective',
            'reviews.reviewer',
        ]);

        return view('monthly-plans.show', compact('monthlyPlan'));
    }

    public function edit(MonthlyPlan $monthlyPlan): View
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        abort_unless(auth()->user()->hasAnyRole(['educator', 'admin']), 403);
        abort_unless($monthlyPlan->isEditableWindow(), 403, 'هذه الخطة ليست قابلة للتعديل حالياً.');

        $monthlyPlan->load([
            'beneficiary',
            'educator',
            'items.annualObjective',
            'reviews.reviewer',
        ]);

        return view('monthly-plans.edit', compact('monthlyPlan'));
    }

    public function update(UpdateMonthlyPlanRequest $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        abort_unless(auth()->user()->hasAnyRole(['educator', 'admin']), 403);
        abort_unless($monthlyPlan->isEditableWindow(), 403, 'هذه الخطة ليست قابلة للتعديل حالياً.');

        $validated = $request->validated();
        $submittedItems = collect($validated['items']);
        $existingIds = $monthlyPlan->items()->pluck('id')->all();

        DB::transaction(function () use ($monthlyPlan, $submittedItems, $existingIds) {
            $keptIds = [];

            foreach ($submittedItems as $itemData) {
                $payload = [
                    'annual_project_objective_id' => $itemData['annual_project_objective_id'] ?? null,
                    'domain_axis' => $itemData['domain_axis'],
                    'monthly_objective' => $itemData['monthly_objective'],
                    'activity' => $itemData['activity'],
                    'resources' => $itemData['resources'] ?? null,
                    'success_criteria' => $itemData['success_criteria'] ?? null,
                ];

                if (!empty($itemData['id'])) {
                    $item = MonthlyPlanItem::query()
                        ->where('monthly_plan_id', $monthlyPlan->id)
                        ->findOrFail($itemData['id']);

                    $item->update($payload);
                    $keptIds[] = $item->id;
                } else {
                    $newItem = $monthlyPlan->items()->create($payload);
                    $keptIds[] = $newItem->id;
                }
            }

            $idsToDelete = array_diff($existingIds, $keptIds);

            if (!empty($idsToDelete)) {
                $monthlyPlan->items()->whereIn('id', $idsToDelete)->delete();
            }

            if (!in_array($monthlyPlan->status, ['draft', 'revision', 'rejected'], true)) {
                $monthlyPlan->update([
                    'status' => 'draft',
                    'approved_at' => null,
                ]);
            }
        });

        activity()
            ->performedOn($monthlyPlan)
            ->causedBy(auth()->user())
            ->event('monthly_plan_saved')
            ->log('تم حفظ الخطة الشهرية كمسودة');

        return redirect()
            ->route('monthly-plans.edit', $monthlyPlan)
            ->with('status', 'تم حفظ الخطة الشهرية بنجاح.');
    }

    public function submit(MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->ensureMonthlyPlanAccess($monthlyPlan);

        abort_unless(auth()->user()->hasAnyRole(['educator', 'admin']), 403);
        abort_unless($monthlyPlan->isEditableWindow(), 403, 'هذه الخطة ليست قابلة للإرسال حالياً.');

        if ($monthlyPlan->items()->count() === 0) {
            return back()->withErrors([
                'submit' => 'لا يمكن إرسال الخطة بدون بنود.',
            ]);
        }

        $monthlyPlan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'approved_at' => null,
        ]);

        activity()
            ->performedOn($monthlyPlan)
            ->causedBy(auth()->user())
            ->event('monthly_plan_submitted')
            ->log('تم إرسال الخطة الشهرية للمراجعة');

        return redirect()
            ->route('monthly-plans.show', $monthlyPlan)
            ->with('status', 'تم إرسال الخطة الشهرية إلى المراجعة.');
    }
}