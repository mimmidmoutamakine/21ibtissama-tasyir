<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'category', 'title', 'is_required', 'file_path', 'needs_update'])]
class EmployeeDocument extends Model
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'needs_update' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
