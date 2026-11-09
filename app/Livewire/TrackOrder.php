<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\AuditLog;
use App\Models\Sale;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Url as UrlParam;
use Livewire\Component;

class TrackOrder extends Component
{
    #[UrlParam(as: 'invoice', history: true)]
    public string $invoice = '';

    #[UrlParam(as: 'phone', history: true)]
    public string $phone = '';

    public ?Sale $sale = null;

    public bool $searched = false;

    public bool $isVerified = false;

    public ?string $errorMessage = null;

    public array $auditTimeline = [];

    public function mount(?string $initialInvoice = null, ?string $initialPhone = null): void
    {
        if ($initialInvoice && empty($this->invoice)) {
            $this->invoice = $initialInvoice;
        }

        if ($initialPhone && empty($this->phone)) {
            $this->phone = $initialPhone;
        }

        if (! empty($this->invoice)) {
            $this->track();
        }
    }

    public function track(): void
    {
        $this->errorMessage = null;
        $this->searched = true;

        $ip = request()->ip() ?: '127.0.0.1';
        $throttleKey = 'track-order-attempts:' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Too many tracking requests. Please wait {$seconds} seconds before trying again.";
            $this->sale = null;
            $this->isVerified = false;
            $this->auditTimeline = [];

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $cleanInvoice = strtoupper(trim($this->invoice));

        if (empty($cleanInvoice)) {
            $this->errorMessage = 'Please enter an invoice or order number to track your wholesale shipment.';
            $this->sale = null;
            $this->isVerified = false;
            $this->auditTimeline = [];

            return;
        }

        // Strict invoice format normalization:
        // Accept ATH-INV-2026-0005, 2026-0005, or 0005
        if (preg_match('/^ATH-INV-\d{4}-\d{4}$/', $cleanInvoice)) {
            $targetInvoice = $cleanInvoice;
        } elseif (preg_match('/^\d{4}-\d{4}$/', $cleanInvoice)) {
            $targetInvoice = "ATH-INV-{$cleanInvoice}";
        } elseif (preg_match('/^\d{4}$/', $cleanInvoice)) {
            $targetInvoice = "ATH-INV-" . now()->year . "-{$cleanInvoice}";
        } else {
            $targetInvoice = $cleanInvoice;
        }

        $sale = Sale::with(['items.product.media', 'payments', 'auditLogs'])
            ->where('invoice_number', $targetInvoice)
            ->first();

        if (! $sale) {
            $this->sale = null;
            $this->isVerified = false;
            $this->auditTimeline = [];
            $this->errorMessage = "No wholesale order was found matching \"{$cleanInvoice}\". Please verify your invoice number and try again.";

            return;
        }

        // Verification check: Phone, email, or authenticated staff
        $cleanPhoneFilter = trim($this->phone);
        $this->isVerified = false;

        if (auth()->check() && (auth()->user()->can('view', $sale) || auth()->user()->isSuperAdmin())) {
            $this->isVerified = true;
        }

        if (! empty($cleanPhoneFilter)) {
            $digitsOnlyFilter = preg_replace('/[^0-9]/', '', $cleanPhoneFilter);
            $digitsOnlySale = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');

            $phoneMatches = false;
            if (! empty($digitsOnlyFilter) && ! empty($digitsOnlySale)) {
                $normFilter = ltrim($digitsOnlyFilter, '0');
                $normSale = ltrim($digitsOnlySale, '0');

                if (str_starts_with($normFilter, '234')) {
                    $normFilter = substr($normFilter, 3);
                }
                if (str_starts_with($normSale, '234')) {
                    $normSale = substr($normSale, 3);
                }

                $phoneMatches = ($normFilter === $normSale) ||
                    str_ends_with($normSale, $normFilter) ||
                    str_ends_with($normFilter, $normSale);
            }

            $emailMatches = ! empty($sale->customer_email) &&
                strtolower($cleanPhoneFilter) === strtolower(trim($sale->customer_email));

            if (! $phoneMatches && ! $emailMatches) {
                $this->sale = null;
                $this->isVerified = false;
                $this->auditTimeline = [];
                $this->errorMessage = "The invoice number {$sale->invoice_number} was found, but the phone or email provided does not match our records for this order.";

                return;
            }

            $this->isVerified = true;
        }

        $this->sale = $sale;
        $this->invoice = $sale->invoice_number;
        $this->buildAuditTimeline();
    }

    public function resetSearch(): void
    {
        $this->invoice = '';
        $this->phone = '';
        $this->sale = null;
        $this->searched = false;
        $this->isVerified = false;
        $this->errorMessage = null;
        $this->auditTimeline = [];
    }

    public function getMaskedCustomerNameProperty(): string
    {
        if (! $this->sale || empty($this->sale->customer_name)) {
            return 'Valued Wholesale Customer';
        }

        if ($this->isVerified) {
            return $this->sale->customer_name;
        }

        $parts = explode(' ', trim($this->sale->customer_name));
        if (count($parts) > 1) {
            $first = $parts[0];
            $lastInitial = mb_substr($parts[count($parts) - 1], 0, 1);

            return "{$first} {$lastInitial}***";
        }

        $first = $parts[0];

        return mb_strlen($first) > 3 ? mb_substr($first, 0, 3) . '***' : $first;
    }

    public function getMaskedShippingAddressProperty(): ?string
    {
        if (! $this->sale || empty($this->sale->shipping_address)) {
            return null;
        }

        if ($this->isVerified) {
            return $this->sale->shipping_address;
        }

        return 'Street address protected (Verify phone to view full address)';
    }

    public function getSignedReceiptUrlProperty(): ?string
    {
        if (! $this->sale) {
            return null;
        }

        $latestRef = $this->sale->payments->last()?->reference;
        if ($latestRef) {
            return route('payment.receipt', [
                'sale' => $this->sale->id,
                'reference' => $latestRef,
            ]);
        }

        return URL::signedRoute('payment.receipt', ['sale' => $this->sale->id]);
    }

    public function getStagesProperty(): array
    {
        if (! $this->sale) {
            return [];
        }

        $status = $this->sale->status instanceof SaleStatus
            ? $this->sale->status
            : SaleStatus::tryFrom((string) $this->sale->status) ?? SaleStatus::Processing;

        $isCancelled = $status === SaleStatus::Cancelled;

        $orderRank = match ($status) {
            SaleStatus::PendingPayment => 1,
            SaleStatus::Processing => 2,
            SaleStatus::Inspection => 3,
            SaleStatus::Dispatched => 4,
            SaleStatus::Delivered, SaleStatus::Completed => 5,
            default => 1,
        };

        return [
            [
                'step' => 1,
                'key' => 'placed',
                'title' => 'Order Placed',
                'short_title' => 'Placed',
                'description' => 'Wholesale order received and registered into AbaTradeHub fulfillment pipeline.',
                'icon' => 'receipt_long',
                'is_completed' => $orderRank >= 1 && ! $isCancelled,
                'is_current' => $orderRank === 1 && ! $isCancelled,
                'timestamp' => $this->sale->order_date?->format('M j, Y') ?? $this->sale->created_at?->format('M j, Y'),
            ],
            [
                'step' => 2,
                'key' => 'processing',
                'title' => 'Sourcing & Production',
                'short_title' => 'Sourcing',
                'description' => 'Batch sourcing underway across Aba artisan clusters and Ariaria manufacturers.',
                'icon' => 'storefront',
                'is_completed' => $orderRank >= 2 && ! $isCancelled,
                'is_current' => $orderRank === 2 && ! $isCancelled,
                'timestamp' => $this->getStageTimestamp('processing'),
            ],
            [
                'step' => 3,
                'key' => 'inspection',
                'title' => '100% Quality Inspection',
                'short_title' => 'Inspection',
                'description' => 'Undergoing 100% pre-shipment quality inspection, fabric check, and count verification at Aba QC facility.',
                'icon' => 'verified',
                'is_completed' => $orderRank >= 3 && ! $isCancelled,
                'is_current' => $orderRank === 3 && ! $isCancelled,
                'timestamp' => $this->getStageTimestamp('inspection'),
            ],
            [
                'step' => 4,
                'key' => 'dispatched',
                'title' => 'Dispatched & In Transit',
                'short_title' => 'Dispatched',
                'description' => 'Passed pre-shipment inspection and handed over for interstate park waybill or regional carrier haulage.',
                'icon' => 'local_shipping',
                'is_completed' => $orderRank >= 4 && ! $isCancelled,
                'is_current' => $orderRank === 4 && ! $isCancelled,
                'timestamp' => $this->getStageTimestamp('dispatched'),
            ],
            [
                'step' => 5,
                'key' => 'delivered',
                'title' => 'Delivered / Completed',
                'short_title' => 'Completed',
                'description' => 'Arrived safely at recipient destination hub / address and confirmed fulfilled.',
                'icon' => 'task_alt',
                'is_completed' => $orderRank >= 5 && ! $isCancelled,
                'is_current' => $orderRank === 5 && ! $isCancelled,
                'timestamp' => $this->getStageTimestamp('completed') ?? $this->getStageTimestamp('delivered'),
            ],
        ];
    }

    protected function getStageTimestamp(string $stage): ?string
    {
        if (! $this->sale) {
            return null;
        }

        foreach ($this->auditTimeline as $event) {
            if ($event['stage_key'] === $stage) {
                return $event['formatted_time'];
            }
        }

        return null;
    }

    protected function buildAuditTimeline(): void
    {
        if (! $this->sale) {
            $this->auditTimeline = [];

            return;
        }

        $events = [];

        // Order creation event
        $events[] = [
            'stage_key' => 'placed',
            'action' => 'created',
            'title' => 'Wholesale Order Registered',
            'description' => "Order #{$this->sale->invoice_number} recorded for destination: {$this->sale->destination_city}, {$this->sale->destination_country}.",
            'created_at' => $this->sale->created_at,
            'formatted_time' => $this->sale->created_at?->format('M j, Y • g:i A'),
            'icon' => 'receipt_long',
            'color' => 'primary',
        ];

        // Audit logs for status and payment changes
        $logs = $this->sale->auditLogs->sortBy('created_at');
        foreach ($logs as $log) {
            $parsed = $this->parseAuditLog($log);
            if ($parsed) {
                $events[] = $parsed;
            }
        }

        // Sort chronological descending (latest first) or ascending
        $this->auditTimeline = array_reverse($events);
    }

    protected function parseAuditLog(AuditLog $log): ?array
    {
        $newValues = $log->new_values ?? [];
        $action = $log->action;

        if ($action === 'payment_recorded' || $action === 'payment_status_changed') {
            return [
                'stage_key' => 'payment',
                'action' => 'payment',
                'title' => 'Payment Verified',
                'description' => $log->description,
                'created_at' => $log->created_at,
                'formatted_time' => $log->created_at?->format('M j, Y • g:i A'),
                'icon' => 'payments',
                'color' => 'secondary',
            ];
        }

        if ($action === 'status_changed' && isset($newValues['status'])) {
            $statusVal = is_object($newValues['status']) && enum_exists(get_class($newValues['status']))
                ? $newValues['status']->value
                : (string) $newValues['status'];

            $title = match ($statusVal) {
                'processing' => 'Sourcing & Production Started',
                'inspection' => 'Arrived for 100% Quality Inspection',
                'dispatched' => 'Dispatched for Transit',
                'delivered' => 'Arrived at Destination',
                'completed' => 'Order Completed & Fulfilled',
                'cancelled' => 'Order Cancelled',
                default => 'Fulfillment Status Updated',
            };

            $icon = match ($statusVal) {
                'processing' => 'storefront',
                'inspection' => 'verified',
                'dispatched' => 'local_shipping',
                'delivered', 'completed' => 'task_alt',
                'cancelled' => 'cancel',
                default => 'update',
            };

            $color = match ($statusVal) {
                'inspection' => 'primary',
                'dispatched' => 'info',
                'delivered', 'completed' => 'secondary',
                'cancelled' => 'error',
                default => 'primary',
            };

            return [
                'stage_key' => $statusVal,
                'action' => 'status_changed',
                'title' => $title,
                'description' => $log->description,
                'created_at' => $log->created_at,
                'formatted_time' => $log->created_at?->format('M j, Y • g:i A'),
                'icon' => $icon,
                'color' => $color,
            ];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.track-order', [
            'stages' => $this->stages,
        ]);
    }
}
