<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt &amp; Wholesale Order Confirmation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fff8f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f1b17; -webkit-font-smoothing: antialiased;">
    @php
        $company = config('abatradehub.name', 'AbaTradeHub');
        $currency = $sale->currency ?: 'NGN';
        $receiptUrl = route('payment.receipt', ['sale' => $sale->id, 'reference' => $payment->reference]);
        $cleanPhone = preg_replace('/\D+/', '', $sale->customer_phone);
        if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 11) {
            $cleanPhone = '234' . substr($cleanPhone, 1);
        }
        $whatsappSupportUrl = \App\Support\Whatsapp::url("Hello {$company}, I have completed online payment for order {$sale->invoice_number}. Please confirm dispatch schedule.");
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff8f5; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; background-color: #ffffff; border-radius: 12px; border: 1px solid #dec0b5; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                    {{-- Top Header Brand Banner --}}
                    <tr>
                        <td style="background-color: #2c694e; padding: 24px 32px; text-align: left;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">
                                            {{ $company }}
                                        </h1>
                                        <p style="margin: 4px 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #aeeecb;">
                                            Official Payment Receipt &bull; Verified Wholesale Order
                                        </p>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; background-color: #aeeecb; color: #002114; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                                            100% Settled
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Content Body --}}
                    <tr>
                        <td style="padding: 32px 32px 24px;">
                            {{-- Greeting & Success Banner --}}
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="36" valign="top">
                                            <div style="background-color: #2c694e; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; font-size: 16px;">
                                                ✓
                                            </div>
                                        </td>
                                        <td style="padding-left: 10px;">
                                            <h2 style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #166534;">
                                                Payment Verified &amp; Order Confirmed
                                            </h2>
                                            <p style="margin: 0; font-size: 13px; color: #15803d; line-height: 1.4;">
                                                Dear <strong>{{ $sale->customer_name }}</strong>, we have received your payment of <strong>{{ $currency }} {{ number_format($payment->amount, 2) }}</strong> via Paystack. Your wholesale batch has been queued for Ariaria hub sourcing and quality inspection.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Payment Summary Grid --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fcf2eb; border: 1px solid #dec0b5; border-radius: 10px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" valign="top" style="padding-bottom: 12px;">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Invoice / Order #</span>
                                                    <span style="font-size: 14px; font-weight: 700; color: #1f1b17;">{{ $sale->invoice_number }}</span>
                                                </td>
                                                <td width="50%" valign="top" style="padding-bottom: 12px;">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Paystack Transaction Ref</span>
                                                    <span style="font-size: 13px; font-weight: 600; color: #1f1b17; font-family: monospace;">{{ $payment->reference }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" valign="top">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Payment Channel</span>
                                                    <span style="font-size: 13px; font-weight: 600; color: #1f1b17;">Paystack (Online Gateway)</span>
                                                </td>
                                                <td width="50%" valign="top">
                                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #8a7268; font-weight: 600; display: block;">Payment Date</span>
                                                    <span style="font-size: 13px; font-weight: 600; color: #1f1b17;">{{ $payment->payment_date?->format('d M, Y') ?? now()->format('d M, Y') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Ordered Wholesale Items Section --}}
                            <h3 style="margin: 0 0 12px; font-size: 15px; font-weight: 700; color: #1f1b17; text-transform: uppercase; letter-spacing: 0.03em;">
                                Ordered Wholesale Items ({{ $sale->items->count() }} {{ $sale->items->count() === 1 ? 'Product' : 'Products' }})
                            </h3>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #dec0b5; border-radius: 10px; overflow: hidden; margin-bottom: 24px;">
                                <thead>
                                    <tr style="background-color: #f6ece5; border-bottom: 1px solid #dec0b5;">
                                        <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Product Item</th>
                                        <th style="padding: 10px 14px; text-align: center; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Qty</th>
                                        <th style="padding: 10px 14px; text-align: right; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #57423a;">Unit Price</th>
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
                                                        <td style="width: 52px; vertical-align: top; padding-right: 12px;">
                                                            @if (! empty($imgUrl))
                                                                <img src="{{ $imgUrl }}" alt="{{ $item->item_name }}" width="52" height="52" style="width: 52px; height: 52px; object-fit: cover; border-radius: 6px; border: 1px solid #dec0b5; display: block;" />
                                                            @else
                                                                <div style="width: 50px; height: 50px; background-color: #f6ece5; border-radius: 6px; border: 1px solid #dec0b5; text-align: center; line-height: 50px; color: #8a7268; font-size: 11px; font-weight: bold;">
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
                                                                <div style="font-size: 11px; color: #2c694e; font-weight: 600; margin-top: 3px;">
                                                                    Specs: {{ $item->specifications }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td style="padding: 12px 14px; text-align: center; vertical-align: top; font-size: 13px; font-weight: 600; color: #1f1b17;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td style="padding: 12px 14px; text-align: right; vertical-align: top; font-size: 12px; color: #57423a;">
                                                {{ $currency }} {{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td style="padding: 12px 14px; text-align: right; vertical-align: top; font-size: 13px; font-weight: 700; color: #1f1b17;">
                                                {{ $currency }} {{ number_format($item->total_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #fcf2eb;">
                                        <td colspan="3" style="padding: 8px 14px; text-align: right; font-size: 12px; color: #57423a;">
                                            Wholesale Subtotal:
                                        </td>
                                        <td style="padding: 8px 14px; text-align: right; font-size: 13px; font-weight: 600; color: #1f1b17;">
                                            {{ $currency }} {{ number_format($sale->subtotal, 2) }}
                                        </td>
                                    </tr>
                                    <tr style="background-color: #fcf2eb;">
                                        <td colspan="3" style="padding: 6px 14px; text-align: right; font-size: 12px; color: #57423a;">
                                            Ariaria Quality Inspection (100% Pre-Shipment):
                                        </td>
                                        <td style="padding: 6px 14px; text-align: right; font-size: 12px; font-weight: 700; color: #2c694e;">
                                            FREE
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f6ece5; border-top: 1px solid #dec0b5;">
                                        <td colspan="3" style="padding: 12px 14px; text-align: right; font-size: 14px; font-weight: 700; color: #1f1b17;">
                                            Total Amount Paid:
                                        </td>
                                        <td style="padding: 12px 14px; text-align: right; font-size: 16px; font-weight: 800; color: #2c694e;">
                                            {{ $currency }} {{ number_format($payment->amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f6ece5;">
                                        <td colspan="3" style="padding: 4px 14px 12px; text-align: right; font-size: 12px; font-weight: 600; color: #8a7268;">
                                            Balance Due:
                                        </td>
                                        <td style="padding: 4px 14px 12px; text-align: right; font-size: 13px; font-weight: 700; color: #1f1b17;">
                                            {{ $currency }} 0.00
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Delivery & Destination Details Card --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fcf2eb; border: 1px solid #dec0b5; border-radius: 10px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <h4 style="margin: 0 0 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #57423a;">
                                            Dispatch &amp; Delivery Destination
                                        </h4>
                                        <p style="margin: 0 0 4px; font-size: 13px; color: #1f1b17;">
                                            <strong>Destination:</strong> {{ $sale->destination_city }}, {{ $sale->destination_country }}
                                        </p>
                                        @if ($sale->shipping_address)
                                            <p style="margin: 0 0 4px; font-size: 13px; color: #1f1b17;">
                                                <strong>Address / Line:</strong> {{ $sale->shipping_address }}
                                            </p>
                                        @endif
                                        <p style="margin: 0; font-size: 13px; color: #1f1b17;">
                                            <strong>Buyer Contact:</strong> {{ $sale->customer_phone }} (WhatsApp)
                                        </p>
                                        @if ($sale->customer_notes)
                                            <p style="margin: 6px 0 0; font-size: 12px; color: #8a7268; font-style: italic;">
                                                Instructions: &ldquo;{{ $sale->customer_notes }}&rdquo;
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Action Call To Action Buttons --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('orders.track', ['invoice' => $sale->invoice_number]) }}" style="display: inline-block; background-color: #c05621; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 14px 28px; border-radius: 10px; text-align: center; margin-right: 8px; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(192,86,33,0.25);">
                                            Track Order Live &rarr;
                                        </a>
                                        <a href="{{ $receiptUrl }}" style="display: inline-block; background-color: #2c694e; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 14px 28px; border-radius: 10px; text-align: center; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(44,105,78,0.25);">
                                            View Official Receipt &rarr;
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 12px;">
                                        <a href="{{ $whatsappSupportUrl }}" style="display: inline-block; color: #2c694e; text-decoration: underline; font-size: 13px; font-weight: 600;">
                                            Contact Sourcing Desk via WhatsApp
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

