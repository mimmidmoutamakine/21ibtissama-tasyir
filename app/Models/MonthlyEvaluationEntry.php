<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'monthly_evaluation_id', 'monthly_plan_item_id', 'calculated_progress_percent', 'educator_progress_percent',
    'educator_remarks', 'specialist_final_decision', 'specialist_remarks',
])]
class MonthlyEvaluationEntry extends Model
{
    public function monthlyEvaluation(): BelongsTo
    {
        return $this->belongsTo(MonthlyEvaluation::class);
    }

    public function monthlyPlanItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlanItem::class);
    }
}
