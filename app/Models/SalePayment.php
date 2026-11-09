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

    /**
     * Standard payment methods supported in AbaTradeHub.
     *
     * @return array<string, string>
     */
    public static function methodOptions(): array
    {
        return [
            'paystack' => 'Paystack (Online Gateway)',
            'bank_transfer' => 'Direct Bank Transfer',
            'cash' => 'Cash',
            'pos' => 'POS / Card',
            'mobile_money' => 'Mobile Money / Transfer',
            'escrow' => 'Escrow',
            'other' => 'Other',
        ];
    }

    /**
     * Human-readable label for a payment method.
     */
    public static function methodLabel(?string $method): string
    {
        return match ($method) {
            'paystack' => 'Paystack Online',
            'bank_transfer' => 'Direct Bank Transfer',
            'cash' => 'Cash',
            'pos' => 'POS / Card',
            'mobile_money' => 'Mobile Money / Transfer',
            'escrow' => 'Escrow',
            default => ucfirst(str_replace('_', ' ', (string) $method)),
        };
    }

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
                $method = strtoupper(str_replace('_', ' ', $payment->payment_method));
                $amountFormatted = number_format($payment->amount, 2);
                $isOnline = empty($payment->recorded_by) && (! auth()->check() || $payment->payment_method === 'paystack');

                if ($isOnline) {
                    $cName = trim($sale->customer_name ?? '') ?: 'Customer';
                    $desc = "Online payment of ₦{$amountFormatted} verified via Paystack ({$cName}) for {$sale->invoice_number}";
                } else {
                    $recorderName = auth()->user()?->name ?? 'Staff';
                    $desc = "Staff {$recorderName} recorded payment of ₦{$amountFormatted} ({$method}) for {$sale->invoice_number}";
                }

                AuditLog::record(
                    action: 'payment_recorded',
                    description: $desc,
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

