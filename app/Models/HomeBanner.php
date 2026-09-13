<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'product_id',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->image_path
                ? asset('storage/'.$this->image_path)
                : null
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}