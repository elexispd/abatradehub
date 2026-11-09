<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'enquiry_id',
        'created_by',
        'updated_by',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_company',
        'shipping_address',
        'destination_city',
        'destination_country',
        'status',
        'payment_status',
        'currency',
        'subtotal',
        'shipping_fee',
        'inspection_fee',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'order_date',
        'due_date',
        'paid_at',
        'customer_notes',
        'internal_notes',
    ];

    protected $casts = [
        'status' => SaleStatus::class,
        'payment_status' => PaymentStatus::class,
        'order_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'inspection_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public ?string $reversal_reason = null;

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->invoice_number)) {
                $sale->invoice_number = self::generateInvoiceNumber();
            }

            if (empty($sale->order_date)) {
                $sale->order_date = now()->toDateString();
            }

            if (empty($sale->created_by) && auth()->check()) {
                $sale->created_by = auth()->id();
            }

            if (empty($sale->updated_by) && auth()->check()) {
                $sale->updated_by = auth()->id();
            }
        });

        static::updating(function (Sale $sale) {
            if (auth()->check()) {
                $sale->updated_by = auth()->id();
            }

            // Enforce: When order is marked completed/delivered, only super-admin can reverse it!
            if ($sale->isDirty('status')) {
                $originalRaw = $sale->getOriginal('status');
                $originalStatus = $originalRaw instanceof SaleStatus ? $originalRaw->value : (string) $originalRaw;
                $newStatus = $sale->status instanceof SaleStatus ? $sale->status->value : (string) $sale->status;

                $wasCompleted = in_array($originalStatus, ['completed', 'delivered'], true);
                $isStillCompleted = in_array($newStatus, ['completed', 'delivered'], true);

                if ($wasCompleted && ! $isStillCompleted) {
                    $user = auth()->user();
                    if (! $user || ! $user->isSuperAdmin()) {
                        throw new \DomainException('A completed order can only be reversed by a Super Admin.');
                    }

                    $reasonText = ! empty($sale->reversal_reason) ? " Reason: {$sale->reversal_reason}" : '';

                    AuditLog::record(
                        action: 'order_reversed',
                        description: "Super Admin {$user->name} reversed completed order {$sale->invoice_number} from [{$originalStatus}] to [{$newStatus}].{$reasonText}",
                        auditable: $sale,
                        oldValues: ['status' => $originalStatus],
                        newValues: [
                            'status' => $newStatus,
                            'reason' => $sale->reversal_reason,
                        ],
                    );
                }
            }
        });

        static::deleting(function (Sale $sale) {
            if ($sale->payment_status === PaymentStatus::Paid || $sale->amount_paid > 0) {
                throw new \DomainException('A paid sale record or sale with recorded payments cannot be deleted.');
            }
        });

        static::saved(function (Sale $sale) {
            // Send thank you / order completion email when order transitions to completed
            if ($sale->wasChanged('status')) {
                $statusVal = $sale->status instanceof SaleStatus ? $sale->status->value : (string) $sale->status;
                if ($statusVal === 'completed' && ! empty($sale->customer_email)) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($sale->customer_email)
                            ->send(new \App\Mail\OrderCompletedThankYouMail($sale));

                        \Illuminate\Support\Facades\Log::info("Thank you email dispatched to {$sale->customer_email} for completed sale #{$sale->invoice_number}");
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Could not dispatch thank you email to {$sale->customer_email} for sale #{$sale->invoice_number}: " . $e->getMessage());
                    }
                }
            }
        });
    }

    public function isCompleted(): bool
    {
        return $this->status instanceof SaleStatus
            ? $this->status->isCompleted()
            : in_array((string) $this->status, ['completed', 'delivered'], true);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "ATH-INV-{$year}-";

        // Find the highest sequence number for this year
        $latest = self::withTrashed()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->value('invoice_number');

        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(bool $save = false): void
    {
        $this->subtotal = (float) $this->items()->sum('total_price');
        $this->total_amount = max(0, $this->subtotal + (float) $this->shipping_fee + (float) $this->inspection_fee - (float) $this->discount_amount);
        $this->amount_paid = (float) $this->payments()->sum('amount');
        $this->balance_due = max(0, $this->total_amount - $this->amount_paid);

        if ($this->amount_paid >= $this->total_amount && $this->total_amount > 0) {
            $this->payment_status = PaymentStatus::Paid;
            if (! $this->paid_at) {
                $this->paid_at = now();
            }
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = PaymentStatus::Partial;
            $this->paid_at = null;
        } else {
            $this->payment_status = PaymentStatus::Unpaid;
            $this->paid_at = null;
        }

        if ($save) {
            $this->saveQuietly();
        }
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)->orderBy('payment_date', 'asc');
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }
}

