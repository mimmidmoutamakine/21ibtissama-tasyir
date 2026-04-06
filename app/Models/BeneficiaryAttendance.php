<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['beneficiary_id', 'educator_id', 'attendance_date', 'status', 'notes'])]
class BeneficiaryAttendance extends Model
{
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
