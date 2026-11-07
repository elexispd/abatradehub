<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

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
}
