<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'parent_id',
    'name_en',
    'name_mm',
    'type',
    'shipping_fee',
    'is_active',
    'sort_order',
])]
class Location extends Model
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this
            ->hasMany(Location::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name_en');
    }

    protected function casts(): array
    {
        return [
            'shipping_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}