<?php

namespace App\Models;

use Database\Factories\PriceTierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTier extends Model
{
    /** @use HasFactory<PriceTierFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'min_quantity', 'max_quantity', 'unit_price'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getRangeLabelAttribute(): string
    {
        return $this->max_quantity
            ? "{$this->min_quantity}-{$this->max_quantity}"
            : "{$this->min_quantity}+";
    }
}
