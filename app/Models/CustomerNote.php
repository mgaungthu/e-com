<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'created_by',
    'note',
    'visibility',
    'is_pinned',
])]
class CustomerNote extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }
}
