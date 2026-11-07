<?php

namespace App\Models;

use Database\Factories\EnquiryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnquiryItem extends Model
{
    /** @use HasFactory<EnquiryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'enquiry_id', 'product_id', 'product_name', 'product_sku', 'quantity', 'note',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
