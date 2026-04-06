<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['beneficiary_id', 'educator_id', 'month_date', 'status', 'submitted_at', 'approved_at', 'revision_summary'])]
class MonthlyPlan extends Model
{
    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MonthlyPlanItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(MonthlyPlanReview::class)->latest('reviewed_at');
    }

    public function dailyEvaluations(): HasMany
    {
        return $this->hasMany(DailyEvaluation::class);
    }

    public function weeklyEvaluations(): HasMany
    {
        return $this->hasMany(WeeklyEvaluation::class);
    }

    public function monthlyEvaluations(): HasMany
    {
        return $this->hasMany(MonthlyEvaluation::class);
    }

    public function isEditableWindow(): bool
    {
        $now = now();
        $lastWeekStart = Carbon::parse($this->month_date)->startOfMonth()->subDays(7);

        return $now->between($lastWeekStart, Carbon::parse($this->month_date)->endOfMonth()) || in_array($this->status, ['rejected', 'revision'], true);
    }

    public function canBeEditedByCurrentUser(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->hasAnyRole(['educator', 'admin']) && $this->isEditableWindow();
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'submitted';
    }
}
