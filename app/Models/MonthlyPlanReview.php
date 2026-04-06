<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['monthly_plan_id', 'reviewer_id', 'decision', 'remarks', 'changes_summary', 'reviewed_at'])]
class MonthlyPlanReview extends Model
{
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function monthlyPlan(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
