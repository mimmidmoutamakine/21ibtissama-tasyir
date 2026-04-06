<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['beneficiary_id', 'monthly_plan_id', 'educator_id', 'specialist_id', 'month_date', 'general_remarks'])]
class MonthlyEvaluation extends Model
{
    protected function casts(): array
    {
        return [
            'month_date' => 'date',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function monthlyPlan(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlan::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MonthlyEvaluationEntry::class);
    }

    public function specialist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }
}
