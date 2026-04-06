<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyEvaluationAttempt extends Model
{
    protected $fillable = [
        'daily_evaluation_entry_id',
        'attempt_number',
        'status_color',
        'progress_percent',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(DailyEvaluationEntry::class, 'daily_evaluation_entry_id');
    }
}