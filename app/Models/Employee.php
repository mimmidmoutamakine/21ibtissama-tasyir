<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'employee_code', 'full_name', 'role_label', 'phone', 'cin', 'hire_date',
    'contract_type', 'qualification', 'status', 'document_requirements', 'organization_notes',
])]
class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'document_requirements' => 'array',
            'organization_notes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function getDossierCompletionPercentAttribute(): int
    {
        $required = $this->documents()->where('is_required', true)->count();
        $uploaded = $this->documents()->where('is_required', true)->whereNotNull('file_path')->count();

        return $required === 0 ? 100 : (int) round(($uploaded / $required) * 100);
    }
}
