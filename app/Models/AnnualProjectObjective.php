<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['annual_project_domain_id', 'objective_text', 'baseline_level', 'target_level', 'display_order'])]
class AnnualProjectObjective extends Model
{
    public function domain(): BelongsTo
    {
        return $this->belongsTo(AnnualProjectDomain::class, 'annual_project_domain_id');
    }

    public function monthlyPlanItems(): HasMany
    {
        return $this->hasMany(MonthlyPlanItem::class);
    }

    public function getProgressPercentAttribute(): int
    {
        $items = $this->monthlyPlanItems()->with('monthlyEvaluationEntries')->get();

        if ($items->isEmpty()) {
            return 0;
        }

        $values = $items
            ->flatMap(fn (MonthlyPlanItem $item) => $item->monthlyEvaluationEntries)
            ->map(fn (MonthlyEvaluationEntry $entry) => $entry->educator_progress_percent ?? $entry->calculated_progress_percent)
            ->filter();

        if ($values->isEmpty()) {
            return 0;
        }

        return (int) round($values->avg());
    }
}
