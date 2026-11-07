<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'item_name',
        'item_sku',
        'quantity',
        'unit_price',
        'total_price',
        'specifications',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (SaleItem $item) {
            $item->total_price = ($item->quantity ?: 1) * ($item->unit_price ?: 0);
        });

        static::saved(function (SaleItem $item) {
            $item->sale?->recalculateTotals(save: true);
        });

        static::deleted(function (SaleItem $item) {
            $item->sale?->recalculateTotals(save: true);
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

