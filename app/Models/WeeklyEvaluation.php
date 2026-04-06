<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['beneficiary_id', 'monthly_plan_id', 'educator_id', 'week_start', 'week_end'])]
class WeeklyEvaluation extends Model
{

    protected $fillable = [
        'beneficiary_id',
        'monthly_plan_id',
        'educator_id',
        'week_start',
        'week_end',
        'general_notes',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
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
        return $this->hasMany(WeeklyEvaluationEntry::class);
    }
}
