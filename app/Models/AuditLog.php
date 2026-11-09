<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record an audit event.
     */
    public static function record(
        string $action,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();
        $userName = $user?->name;
        $userEmail = $user?->email;

        if (! $user) {
            if ($auditable instanceof Sale) {
                $cName = trim($auditable->customer_name ?? '');
                $userName = $cName !== '' ? "Online Customer ({$cName})" : 'Online Customer';
                $userEmail = $auditable->customer_email;
            } elseif ($auditable instanceof SalePayment) {
                $sale = $auditable->sale;
                $cName = trim($sale?->customer_name ?? '');
                if ($auditable->payment_method === 'paystack') {
                    $userName = $cName !== '' ? "Online Customer ({$cName}) via Paystack" : 'Paystack Gateway';
                } else {
                    $userName = $cName !== '' ? "Customer ({$cName})" : 'Payment Gateway';
                }
                $userEmail = $sale?->customer_email;
            } else {
                $userName = 'System / Online Order';
            }
        }

        return static::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}

