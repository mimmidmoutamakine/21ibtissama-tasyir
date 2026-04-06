<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['beneficiary_id', 'monthly_plan_id', 'educator_id', 'evaluation_date', 'general_notes'])]
class DailyEvaluation extends Model
{
    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
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

    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DailyEvaluationEntry::class);
    }
}
