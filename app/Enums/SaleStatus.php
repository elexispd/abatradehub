<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SaleStatus: string implements HasColor, HasLabel
{
    case PendingPayment = 'pending_payment';
    case Processing = 'processing';
    case Inspection = 'inspection';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::Processing => 'Processing / Sourcing',
            self::Inspection => 'Quality Inspection',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::Processing => 'info',
            self::Inspection => 'primary',
            self::Dispatched => 'gray',
            self::Delivered => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::Completed, self::Delivered], true);
    }
}

