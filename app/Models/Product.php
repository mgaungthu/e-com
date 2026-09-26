<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'restock_eta',
        'image_path',
        'is_active',
        'is_featured',
        'is_new_arrival',
        'is_promotion',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'restock_eta' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_promotion' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'stock_status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $primaryImage = $this->relationLoaded('primaryImage')
                ? $this->primaryImage
                : null;

            $path = $primaryImage?->path ?? $this->image_path;

            return $path ? asset('storage/'.$path) : null;
        });
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favouritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'product_favourites',
        )->withTimestamps();
    }
}
