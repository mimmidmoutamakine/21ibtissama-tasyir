<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['title', 'archiveable_type', 'archiveable_id', 'category', 'file_path', 'notes'])]
class ArchiveFile extends Model
{
    public function archiveable(): MorphTo
    {
        return $this->morphTo();
    }
}
