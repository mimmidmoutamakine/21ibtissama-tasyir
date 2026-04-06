<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyEvaluationEntry extends Model
{
    protected $fillable = [
        'weekly_evaluation_id',
        'monthly_plan_item_id',
        'week_1_status_color',
        'week_1_progress_percent',
        'week_2_status_color',
        'week_2_progress_percent',
        'week_3_status_color',
        'week_3_progress_percent',
        'week_4_status_color',
        'week_4_progress_percent',
        'note',
        'is_manually_edited',
    ];

    public function weeklyEvaluation(): BelongsTo
    {
        return $this->belongsTo(WeeklyEvaluation::class);
    }

    public function monthlyPlanItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlanItem::class);
    }

    public function dailyEvaluation(): BelongsTo
    {
        return $this->belongsTo(DailyEvaluation::class);
    }

    public function attempts()
    {
        return $this->hasMany(DailyEvaluationAttempt::class);
    }
}