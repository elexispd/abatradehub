<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'category_id', 'supplier_id', 'name', 'slug', 'sku', 'short_description',
        'description', 'moq', 'moq_unit', 'base_price', 'currency',
        'lead_time_min_days', 'lead_time_max_days', 'stock_quantity',
        'low_stock_threshold', 'status', 'is_featured', 'published_at',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'base_price' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->status === ProductStatus::Published && ! $product->published_at) {
                $product->published_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(PriceTier::class)->orderBy('min_quantity');
    }

    public function enquiryItems(): HasMany
    {
        return $this->hasMany(EnquiryItem::class)->latest();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Published)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereNotNull('stock_quantity')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function getUrlAttribute(): string
    {
        $categorySlug = $this->category?->slug ?? 'all';

        return url("/products/{$categorySlug}/{$this->slug}");
    }

    public function getWhatsappUrlAttribute(): string
    {
        $url = $this->url;
        $company = config('abatradehub.name');
        $message = "Hello {$company}, I want to enquire about bulk sourcing for {$this->name} (SKU: {$this->sku}): {$url}";

        return \App\Support\Whatsapp::url($message);
    }

    /** Lowest unit price across the base price and all price tiers. */
    public function getStartingPriceAttribute(): ?float
    {
        $prices = $this->priceTiers->pluck('unit_price')->push($this->base_price)->filter(fn ($p) => $p !== null);

        return $prices->isEmpty() ? null : (float) $prices->min();
    }

    /** Human readable lead time, e.g. "5-7 days". */
    public function getLeadTimeLabelAttribute(): ?string
    {
        $min = $this->lead_time_min_days;
        $max = $this->lead_time_max_days;

        if (! $min && ! $max) {
            return null;
        }

        if (! $max || $min === $max) {
            return ($min ?? $max).' days';
        }

        return "{$min}-{$max} days";
    }

    public function toQuickViewArray(): array
    {
        $images = $this->getMedia('images')->map(function ($media) {
            return [
                'card' => $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                'large' => $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category_name' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'url' => $this->url,
            'whatsapp_url' => $this->whatsapp_url,
            'moq' => $this->moq,
            'moq_unit' => $this->moq_unit,
            'lead_time' => $this->lead_time_label ?? '3-5 days',
            'starting_price' => $this->starting_price,
            'starting_price_formatted' => \App\Support\Money::format($this->starting_price, $this->currency),
            'base_price_formatted' => \App\Support\Money::format($this->base_price, $this->currency),
            'currency' => $this->currency,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'market_location' => $this->supplier?->market_location ?? 'Ariaria International Market, Aba',
            'price_tiers' => $this->priceTiers->map(fn ($tier) => [
                'range' => $tier->range_label,
                'price_formatted' => \App\Support\Money::format($tier->unit_price, $this->currency),
            ])->values()->all(),
            'images' => $images->values()->all(),
            'primary_image' => $images->first()['card'] ?? null,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->fit(Fit::Crop, 300, 300)
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('card')
            ->format('webp')
            ->fit(Fit::Crop, 600, 600)
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('large')
            ->format('webp')
            ->fit(Fit::Max, 1400, 1400)
            ->performOnCollections('images')
            ->nonQueued();
    }
}

