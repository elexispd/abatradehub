<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'recorded_by',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (SalePayment $payment) {
            if (empty($payment->recorded_by) && auth()->check()) {
                $payment->recorded_by = auth()->id();
            }
        });

        static::created(function (SalePayment $payment) {
            $sale = $payment->sale;
            if ($sale) {
                $recorderName = auth()->user()?->name ?? 'Staff';
                $method = strtoupper(str_replace('_', ' ', $payment->payment_method));
                $amountFormatted = number_format($payment->amount, 2);

                AuditLog::record(
                    action: 'payment_recorded',
                    description: "{$recorderName} recorded payment of ₦{$amountFormatted} ({$method}) for {$sale->invoice_number}",
                    auditable: $sale,
                    newValues: [
                        'amount' => (float) $payment->amount,
                        'method' => $payment->payment_method,
                        'reference' => $payment->reference,
                        'notes' => $payment->notes,
                        'recorded_by' => $payment->recorded_by,
                    ],
                );
            }
        });

        static::deleting(function (SalePayment $payment) {
            if ($payment->sale?->payment_status === \App\Enums\PaymentStatus::Paid) {
                throw new \DomainException("Payments on a fully paid sale cannot be deleted.");
            }
        });

        static::saved(function (SalePayment $payment) {
            $payment->sale?->recalculateTotals(save: true);
        });

        static::deleted(function (SalePayment $payment) {
            $payment->sale?->recalculateTotals(save: true);
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

