<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['beneficiary_id', 'author_id', 'meeting_month', 'general_notes', 'difficulties', 'recommendations'])]
class PeriodicMeeting extends Model
{
    protected function casts(): array
    {
        return [
            'meeting_month' => 'date',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
