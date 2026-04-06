<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['beneficiary_id', 'specialist_id', 'service_id', 'report_date', 'specialty', 'structured_fields', 'summary', 'recommendations'])]
class SpecialistReport extends Model
{
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'structured_fields' => 'array',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function specialist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
