<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You! Your Wholesale Order is Completed</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fff8f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f1b17; -webkit-font-smoothing: antialiased;">
    @php
        $company = config('abatradehub.name', 'AbaTradeHub');
        $currency = $sale->currency ?: 'NGN';
        $latestPayment = $sale->payments->last();
        if ($latestPayment && $latestPayment->reference) {
            $receiptUrl = route('payment.receipt', ['sale' => $sale->id, 'reference' => $latestPayment->reference]);
        } else {
            $receiptUrl = \Illuminate\Support\Facades\URL::signedRoute('payment.receipt', ['sale' => $sale->id]);
        }
        $catalogUrl = route('products.index');
        $whatsappSupportUrl = \App\Support\Whatsapp::url("Hello {$company}, my wholesale order {$sale->invoice_number} is completed. I would like to inquire about my next restock batch.");
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff8f5; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; background-color: #ffffff; border-radius: 12px; border: 1px solid #dec0b5; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                    {{-- Header Brand Banner --}}
                    <tr>
                        <td style="background-color: #2c694e; padding: 26px 32px; text-align: left;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">
                                            {{ $company }}
                                        </h1>
                                        <p style="margin: 4px 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #aeeecb;">
                                            Direct B2B Manufacturing &bull; Wholesale Order Completed
                                        </p>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; background-color: #aeeecb; color: #002114; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                                            Completed &amp; Fulfilled
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Content Body --}}
                    <tr>
                        <td style="padding: 32px 32px 24px;">
                            {{-- Celebration & Thank You Banner --}}
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; margin-bottom: 24px; text-align: left;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="38" valign="top">
                                            <div style="background-color: #2c694e; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 32px; font-weight: bold; font-size: 18px;">
                                                ✓
                                            </div>
                                        </td>
                                        <td style="padding-left: 12px;">
                                            <h2 style="margin: 0 0 6px; font-size: 18px; font-weight: 700; color: #166534;">
                                                Thank You for Your Wholesale Order!
                                            </h2>
                                            <p style="margin: 0; font-size: 13px; color: #15803d; line-height: 1.5;">
                                                Dear <strong>{{ $sale->customer_name }}</strong>, your wholesale order <strong>#{{ $sale->invoice_number }}</strong> has been fully completed and dispatched. We sincerely appreciate your business and partnership.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Order Highlights Card --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fcf2eb; border: 1px solid #dec0b5; border-radius: 10px; margin-bottom: 26px;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" valign="top" style="padding-bottom: 12px;">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Invoice / Order #</span>
                                                    <span style="font-size: 14px; font-weight: 700; color: #1f1b17;">#{{ $sale->invoice_number }}</span>
                                                </td>
                                                <td width="50%" valign="top" style="padding-bottom: 12px;">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Completion Date</span>
                                                    <span style="font-size: 13px; font-weight: 600; color: #1f1b17;">{{ now()->format('d M, Y') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" valign="top">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Destination City</span>
                                                    <span style="font-size: 13px; font-weight: 600; color: #1f1b17;">{{ $sale->destination_city }}, {{ $sale->destination_country }}</span>
                                                </td>
                                                <td width="50%" valign="top">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Total Wholesale Batch</span>
                                                    <span style="font-size: 13px; font-weight: 700; color: #2c694e;">{{ $sale->items->sum('quantity') }} units &bull; {{ $currency }} {{ number_format($sale->total_amount, 2) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Fulfilled Items Summary Table --}}
                            <h3 style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #1f1b17; text-transform: uppercase; letter-spacing: 0.03em;">
                                Fulfilled Wholesale Items
                            </h3>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #dec0b5; border-radius: 10px; overflow: hidden; margin-bottom: 24px;">
                                <thead>
                                    <tr style="background-color: #f6ece5; border-bottom: 1px solid #dec0b5;">
                                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Product Item</th>
                                        <th style="padding: 10px 14px; text-align: center; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Fulfilled Qty</th>
                                        <th style="padding: 10px 14px; text-align: right; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sale->items as $item)
                                        @php
                                            $product = $item->product;
                                            $imgUrl = null;
                                            if ($product) {
                                                $imgUrl = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images');
                                            }
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f1e6e0;">
                                            <td style="padding: 12px 14px; vertical-align: top;">
                                                <table role="presentation" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="width: 48px; vertical-align: top; padding-right: 12px;">
                                                            @if (! empty($imgUrl))
                                                                <img src="{{ $imgUrl }}" alt="{{ $item->item_name }}" width="48" height="48" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #dec0b5; display: block;" />
                                                            @else
                                                                <div style="width: 48px; height: 48px; background-color: #f6ece5; border-radius: 6px; border: 1px solid #dec0b5; text-align: center; line-height: 48px; color: #8a7268; font-size: 11px; font-weight: bold;">
                                                                    ATH
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td style="vertical-align: top;">
                                                            <div style="font-size: 13px; font-weight: 700; color: #1f1b17; line-height: 1.3;">
                                                                {{ $item->item_name }}
                                                            </div>
                                                            @if ($item->item_sku)
                                                                <div style="font-size: 11px; color: #8a7268; margin-top: 2px;">
                                                                    SKU: {{ $item->item_sku }}
                                                                </div>
                                                            @endif
                                                            @if ($item->specifications)
                                                                <div style="font-size: 11px; color: #2c694e; font-weight: 600; margin-top: 2px;">
                                                                    {{ $item->specifications }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td style="padding: 12px 14px; text-align: center; vertical-align: top; font-size: 13px; font-weight: 600; color: #1f1b17;">
                                                {{ $item->quantity }} pcs
                                            </td>
                                            <td style="padding: 12px 14px; text-align: right; vertical-align: top; font-size: 13px; font-weight: 700; color: #1f1b17;">
                                                {{ $currency }} {{ number_format($item->total_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- Reorder & Sourcing Partnership Box --}}
                            <div style="background-color: #fff8f5; border: 1px solid #dec0b5; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; text-align: left;">
                                <h4 style="margin: 0 0 6px; font-size: 14px; font-weight: 700; color: #9f3e07;">
                                    Ready to Restock or Expand Your Line?
                                </h4>
                                <p style="margin: 0; font-size: 13px; color: #57423a; line-height: 1.5;">
                                    We maintain continuous production runs across footwear, senator sets, kaftans, and leather goods. Contact your dedicated sourcing desk or explore our live catalog to plan your next shipment.
                                </p>
                            </div>

                            {{-- Action Call To Action Buttons --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $catalogUrl }}" style="display: inline-block; background-color: #2c694e; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 14px 28px; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(44,105,78,0.25);">
                                            Browse Catalog to Re-order &rarr;
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 10px;">
                                        <a href="{{ route('orders.track', ['invoice' => $sale->invoice_number]) }}" style="display: inline-block; color: #9f3e07; text-decoration: underline; font-size: 13px; font-weight: 600; margin-right: 14px;">
                                            View Delivery &amp; Order Details
                                        </a>
                                        <span style="color: #dec0b5;">&bull;</span>
                                        <a href="{{ $receiptUrl }}" style="display: inline-block; color: #9f3e07; text-decoration: underline; font-size: 13px; font-weight: 600; margin-right: 14px; margin-left: 14px;">
                                            Download Official Receipt
                                        </a>
                                        <span style="color: #dec0b5;">&bull;</span>
                                        <a href="{{ $whatsappSupportUrl }}" style="display: inline-block; color: #2c694e; text-decoration: underline; font-size: 13px; font-weight: 600; margin-left: 14px;">
                                            WhatsApp Sourcing Desk
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer Note --}}
                    <tr>
                        <td style="background-color: #f6ece5; padding: 20px 32px; border-top: 1px solid #dec0b5; text-align: center;">
                            <p style="margin: 0 0 4px; font-size: 12px; color: #57423a; font-weight: 600;">
                                {{ $company }} &bull; Direct B2B Aba Manufacturing &amp; Wholesale Exports
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #8a7268; line-height: 1.4;">
                                Ariaria International Market &bull; Faulks Road / Factory Line &bull; Aba, Abia State, Nigeria
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

