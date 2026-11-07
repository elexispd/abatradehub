<?php

namespace App\Support;

class Money
{
    private const SYMBOLS = [
        'NGN' => '₦',
        'USD' => '$',
        'GHS' => 'GH₵',
        'KES' => 'KSh ',
        'XOF' => 'CFA ',
        'ZAR' => 'R',
    ];

    public static function format(float|int|string|null $amount, ?string $currency = null): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $currency = strtoupper($currency ?: config('abatradehub.currency', 'NGN'));
        $amount = (float) $amount;
        $decimals = floor($amount) === $amount ? 0 : 2;

        return (self::SYMBOLS[$currency] ?? $currency.' ').number_format($amount, $decimals);
    }
}

