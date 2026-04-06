<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['stock_item_id', 'movement_type', 'quantity', 'movement_date', 'notes'])]
class StockMovement extends Model
{
    protected function casts(): array
    {
        return [
            'movement_date' => 'date',
        ];
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
