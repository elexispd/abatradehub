<?php

/*
|--------------------------------------------------------------------------
| AbaTradeHub company details
|--------------------------------------------------------------------------
| Everything customer-facing that is not catalog data lives here (and in
| .env) so it is never hard-coded in views.
*/

return [
    'name' => env('COMPANY_NAME', 'AbaTradeHub'),
    'tagline' => env('COMPANY_TAGLINE', 'Wholesale Sourcing'),
    'description' => env(
        'COMPANY_DESCRIPTION',
        'We connect retailers and resellers across Africa with trusted manufacturers and traders in Aba. We are the middleman you can trust: we source, check and deliver.'
    ),

    // International format, digits only, no "+" (used for wa.me links).
    'whatsapp_number' => env('COMPANY_WHATSAPP', '2348001234567'),
    'whatsapp_display' => env('COMPANY_WHATSAPP_DISPLAY', '+234 800 123 4567'),

    'email' => env('COMPANY_EMAIL', 'wholesale@abatradehub.com'),
    // Where new enquiry notifications are sent (Phase 5).
    'enquiry_notification_email' => env('COMPANY_ENQUIRY_EMAIL', env('COMPANY_EMAIL', 'wholesale@abatradehub.com')),

    'address' => env('COMPANY_ADDRESS', 'Aba, Abia State, Nigeria'),
    'hours' => env('COMPANY_HOURS', 'Mon - Sat: 8:00 AM - 6:00 PM (WAT)'),
    'topbar' => env('COMPANY_TOPBAR', 'Nationwide delivery across Nigeria & international shipping across Africa | Bulk order support on WhatsApp'),

    'currency' => env('COMPANY_CURRENCY', 'NGN'),

    // Secure custom administrative portal URL prefix (replaces generic /admin)
    'admin_path' => env('ADMIN_PATH', 'hub-ops'),

    // Supported delivery / enquiry countries
    'countries' => [
        'Nigeria',
        'Ghana',
        'Benin Republic',
        "Cote d'Ivoire",
        'Sierra Leone',
        'Other African Country',
    ],

    // Banking details for customer invoices
    'bank' => [
        'name' => env('BANK_NAME', 'First Bank of Nigeria'),
        'account_name' => env('BANK_ACCOUNT_NAME', 'AbaTradeHub Enterprise'),
        'account_number' => env('BANK_ACCOUNT_NUMBER', '2034567890'),
        'instructions' => env('BANK_PAYMENT_INSTRUCTIONS', 'Please quote your Invoice Number as reference and share payment receipt via WhatsApp.'),
    ],

    // Optional testimonial for the home page. Set to null (or leave name empty) to hide the section.
    // The default below is SAMPLE copy from the design: replace it with a real customer quote before launch.
    'testimonial' => [
        'name' => env('TESTIMONIAL_NAME', 'Nkechi O.'),
        'role' => env('TESTIMONIAL_ROLE', 'Boutique Owner, Bodija, Ibadan'),
        'quote' => env('TESTIMONIAL_QUOTE', 'I used to travel from Ibadan to Aba every month just to buy stock. Now AbaTradeHub inspects my items and sends them through the transport park reliably. My shop never runs out of new designs.'),
    ],
];

