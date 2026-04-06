<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['monthly_plan_id', 'annual_project_objective_id', 'domain_axis', 'monthly_objective', 'activity', 'resources', 'success_criteria'])]
class MonthlyPlanItem extends Model
{
    public function monthlyPlan(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlan::class);
    }

    public function annualObjective(): BelongsTo
    {
        return $this->belongsTo(AnnualProjectObjective::class, 'annual_project_objective_id');
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(DailyEvaluationEntry::class);
    }

    public function weeklyEvaluationEntries(): HasMany
    {
        return $this->hasMany(WeeklyEvaluationEntry::class);
    }

    public function monthlyEvaluationEntries(): HasMany
    {
        return $this->hasMany(MonthlyEvaluationEntry::class);
    }
}
