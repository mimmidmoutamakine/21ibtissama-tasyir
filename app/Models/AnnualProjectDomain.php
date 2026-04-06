<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['annual_project_id', 'name', 'display_order'])]
class AnnualProjectDomain extends Model
{
    public function annualProject(): BelongsTo
    {
        return $this->belongsTo(AnnualProject::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(AnnualProjectObjective::class)->orderBy('display_order');
    }
}
