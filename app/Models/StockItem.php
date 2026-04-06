<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'unit', 'quantity', 'alert_threshold', 'location', 'notes'])]
class StockItem extends Model
{
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getIsAlertAttribute(): bool
    {
        return $this->quantity <= $this->alert_threshold;
    }
}
