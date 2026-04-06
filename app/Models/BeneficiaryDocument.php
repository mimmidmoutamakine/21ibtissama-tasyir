<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['beneficiary_id', 'section', 'category', 'title', 'file_path', 'mime_type', 'is_required', 'needs_update', 'uploaded_at', 'notes'])]
class BeneficiaryDocument extends Model
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'needs_update' => 'boolean',
            'uploaded_at' => 'date',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
