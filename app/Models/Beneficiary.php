<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'lead_id',
    'internal_code',
    'first_name_ar',
    'last_name_ar',
    'first_name_fr',
    'last_name_fr',
    'photo_path',
    'date_of_birth',
    'place_of_birth',
    'gender',
    'address',
    'guardian_details',
    'phones',
    'disability_details',
    'schooling_details',
    'admission_date',
    'exit_date',
    'benefit_type',
    'primary_educator_id',
    'program_name',
    'status',
    'notes',
    'is_active',
    'can_assigned_educator_edit',
    'left_at',
    'left_reason',
    'left_by',
])]
class Beneficiary extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'exit_date' => 'date',
            'left_at' => 'datetime',
            'guardian_details' => 'array',
            'phones' => 'array',
            'disability_details' => 'array',
            'schooling_details' => 'array',
            'is_active' => 'boolean',
            'can_assigned_educator_edit' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function primaryEducator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_educator_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BeneficiaryDocument::class);
    }

    public function serviceAssignments(): HasMany
    {
        return $this->hasMany(BeneficiaryServiceAssignment::class);
    }

    public function annualProjects(): HasMany
    {
        return $this->hasMany(AnnualProject::class);
    }

    public function monthlyPlans(): HasMany
    {
        return $this->hasMany(MonthlyPlan::class);
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

    public function periodicMeetings(): HasMany
    {
        return $this->hasMany(PeriodicMeeting::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(BeneficiaryAttendance::class);
    }

    public function specialistReports(): HasMany
    {
        return $this->hasMany(SpecialistReport::class);
    }

    public function leftByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'left_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name_ar} {$this->last_name_ar}");
    }

    public function getRequiredDocumentsCountAttribute(): int
    {
        return $this->documents()->where('is_required', true)->count();
    }

    public function getUploadedRequiredDocumentsCountAttribute(): int
    {
        return $this->documents()->where('is_required', true)->whereNotNull('file_path')->count();
    }

    public function getDossierCompletionPercentAttribute(): int
    {
        $required = max($this->required_documents_count, 1);

        return (int) round(($this->uploaded_required_documents_count / $required) * 100);
    }

    public function getDossierStatusAttribute(): string
    {
        return match (true) {
            $this->dossier_completion_percent >= 100 => 'complete',
            $this->dossier_completion_percent >= 80 => 'almost_complete',
            $this->dossier_completion_percent >= 40 => 'partially_complete',
            default => 'incomplete',
        };
    }

    public function getFatherPhoneAttribute(): ?string
    {
        return data_get($this->guardian_details, 'father_phone');
    }

    public function getMotherPhoneAttribute(): ?string
    {
        return data_get($this->guardian_details, 'mother_phone');
    }

    public function getGuardianPhoneAttribute(): ?string
    {
        return data_get($this->guardian_details, 'guardian_phone');
    }

    public function getWhatsappFatherUrlAttribute(): ?string
    {
        return $this->buildWhatsappUrl($this->father_phone);
    }

    public function getWhatsappMotherUrlAttribute(): ?string
    {
        return $this->buildWhatsappUrl($this->mother_phone);
    }

    public function getWhatsappGuardianUrlAttribute(): ?string
    {
        return $this->buildWhatsappUrl($this->guardian_phone);
    }

    private function buildWhatsappUrl(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);

        if (! $normalized) {
            return null;
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '212' . substr($normalized, 1);
        } elseif (! str_starts_with($normalized, '212')) {
            $normalized = '212' . $normalized;
        }

        return 'https://wa.me/' . $normalized;
    }
}