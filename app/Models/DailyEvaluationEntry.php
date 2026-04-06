<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyEvaluationEntry extends Model
{
    protected $fillable = [
        'daily_evaluation_id',
        'monthly_plan_item_id',
        'note',
    ];

    public function dailyEvaluation(): BelongsTo
    {
        return $this->belongsTo(DailyEvaluation::class);
    }

    public function monthlyPlanItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlanItem::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(DailyEvaluationAttempt::class, 'daily_evaluation_entry_id');
    }
}