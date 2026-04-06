<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'status'])]
class Service extends Model
{
    public function assignments(): HasMany
    {
        return $this->hasMany(BeneficiaryServiceAssignment::class);
    }
}
