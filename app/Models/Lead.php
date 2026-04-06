<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'first_name_ar', 'last_name_ar', 'first_name_fr', 'last_name_fr', 'age', 'date_of_birth',
    'place_of_birth', 'gender', 'address', 'father_full_name', 'father_phone', 'father_cin',
    'mother_full_name', 'mother_phone', 'mother_cin', 'guardian_cin', 'birth_certificate_reference',
    'family_status', 'disability_type', 'disability_degree', 'disability_classification',
    'school_level', 'school_institution', 'registration_date', 'exit_date', 'benefit_type',
    'program_service_domain', 'document_checklist', 'uploaded_documents', 'status', 'notes',
])]
class Lead extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'registration_date' => 'date',
            'exit_date' => 'date',
            'document_checklist' => 'array',
            'uploaded_documents' => 'array',
        ];
    }

    public function beneficiary(): HasOne
    {
        return $this->hasOne(Beneficiary::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name_ar} {$this->last_name_ar}");
    }
}
