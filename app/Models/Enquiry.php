<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Enquiry extends Model implements HasMedia
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'reference', 'name', 'email', 'phone', 'company', 'country', 'city',
        'message', 'source', 'status', 'admin_notes', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'contacted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Enquiry $enquiry) {
            $enquiry->reference ??= static::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'ENQ-'.now()->format('ymd').'-'.strtoupper(\Illuminate\Support\Str::random(4));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function items(): HasMany
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('reference_photos')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function getSubjectAttribute(): string
    {
        if ($this->items->isNotEmpty()) {
            return $this->items->map(fn ($item) => "{$item->product_name} ({$item->quantity} pcs)")->join(', ');
        }

        return 'Instant Quote Request';
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->items->isNotEmpty() || $this->source === 'catalog') {
            return 'Product Enquiry';
        }

        if ($this->source === 'whatsapp') {
            return 'WhatsApp';
        }

        if ($this->source === 'custom_photo') {
            return 'Photo Request';
        }

        return 'Instant Quote';
    }

    public function getFirstProductImageAttribute(): ?string
    {
        $item = $this->items->first();
        if (! $item) {
            return null;
        }

        $product = $item->relationLoaded('product') ? $item->product : ($item->product_id ? Product::find($item->product_id) : null);
        if (! $product) {
            return null;
        }

        $media = $product->getFirstMedia('images');
        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion('thumb')
            ? $media->getUrl('thumb')
            : $media->getUrl();
    }
}
