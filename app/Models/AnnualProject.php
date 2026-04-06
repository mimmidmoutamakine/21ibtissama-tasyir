<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['beneficiary_id', 'year_label', 'schooling_space_type', 'initial_situation_summary', 'team_participants', 'family_participation', 'approval_status', 'attachments'])]
class AnnualProject extends Model
{
    protected function casts(): array
    {
        return [
            'team_participants' => 'array',
            'attachments' => 'array',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(AnnualProjectDomain::class)->orderBy('display_order');
    }
}
