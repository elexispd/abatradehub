<?php

namespace App\Support;

class Whatsapp
{
    /** wa.me link for the company number, optionally pre-filled with a message. */
    public static function url(?string $message = null): string
    {
        $number = preg_replace('/\D+/', '', (string) config('abatradehub.whatsapp_number'));
        $url = 'https://wa.me/'.$number;

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }

    public static function display(): string
    {
        return (string) config('abatradehub.whatsapp_display');
    }
}

