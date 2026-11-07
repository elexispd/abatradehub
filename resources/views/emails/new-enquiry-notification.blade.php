<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Wholesale Enquiry</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fff8f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f1b17; -webkit-font-smoothing: antialiased;">
    @php
        $cleanPhone = preg_replace('/\D+/', '', $enquiry->phone);
        if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 11) {
            $cleanPhone = '234' . substr($cleanPhone, 1);
        }
        $buyerWhatsAppUrl = "https://wa.me/{$cleanPhone}";
        $adminUrl = url("/admin/enquiries/{$enquiry->id}/edit");
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff8f5; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; border: 1px solid #dec0b5; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #9f3e07; padding: 24px 32px; text-align: left;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">
                                            {{ config('abatradehub.name', 'AbaTradeHub') }}
                                        </h1>
                                        <p style="margin: 4px 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffdbcd;">
                                            {{ config('abatradehub.tagline', 'Wholesale Sourcing') }} &bull; Staff Notification
                                        </p>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; background-color: #ffdbcd; color: #360f00; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">
                                            {{ $enquiry->reference }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px;">
                            <h2 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: #1f1b17;">
                                New Wholesale Enquiry Received
                            </h2>
                            <p style="margin: 0 0 24px; font-size: 14px; color: #57423a; line-height: 1.5;">
                                A new bulk sourcing enquiry has just been submitted on the website. Details are listed below for immediate follow-up:
                            </p>

                            {{-- Buyer Details Card --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fcf2eb; border-radius: 8px; border: 1px solid #dec0b5; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; color: #57423a; width: 140px;"><strong>Buyer / Boutique:</strong></td>
                                                <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #1f1b17;">{{ $enquiry->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; color: #57423a;"><strong>WhatsApp Phone:</strong></td>
                                                <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #2c694e;">
                                                    <a href="{{ $buyerWhatsAppUrl }}" target="_blank" style="color: #2c694e; text-decoration: underline;">
                                                        {{ $enquiry->phone }} (Chat)
                                                    </a>
                                                </td>
                                            </tr>
                                            @if ($enquiry->email)
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 13px; color: #57423a;"><strong>Email:</strong></td>
                                                    <td style="padding: 4px 0; font-size: 14px; color: #1f1b17;">{{ $enquiry->email }}</td>
                                                </tr>
                                            @endif
                                            @if ($enquiry->city)
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 13px; color: #57423a;"><strong>Destination (City/Park):</strong></td>
                                                    <td style="padding: 4px 0; font-size: 14px; color: #1f1b17;">{{ $enquiry->city }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; color: #57423a;"><strong>Received At:</strong></td>
                                                <td style="padding: 4px 0; font-size: 13px; color: #57423a;">
                                                    {{ $enquiry->created_at->format('M j, Y - g:i A') }} ({{ $enquiry->created_at->diffForHumans() }})
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Requested Products (if any) --}}
                            @if ($enquiry->items->isNotEmpty())
                                <h3 style="margin: 0 0 12px; font-size: 15px; font-weight: 700; color: #1f1b17;">
                                    Requested Products ({{ $enquiry->items->count() }})
                                </h3>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #dec0b5; border-radius: 8px; overflow: hidden; margin-bottom: 24px;">
                                    <thead style="background-color: #f6ece5;">
                                        <tr>
                                            <th align="left" style="padding: 10px 12px; font-size: 12px; color: #57423a; text-transform: uppercase;">Product</th>
                                            <th align="center" style="padding: 10px 12px; font-size: 12px; color: #57423a; text-transform: uppercase;">SKU</th>
                                            <th align="right" style="padding: 10px 12px; font-size: 12px; color: #57423a; text-transform: uppercase;">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody style="background-color: #ffffff;">
                                        @foreach ($enquiry->items as $item)
                                            <tr style="border-top: 1px solid #dec0b5;">
                                                <td style="padding: 12px; font-size: 13px; font-weight: 600; color: #1f1b17;">
                                                    @if ($item->product)
                                                        <a href="{{ $item->product->url }}" target="_blank" style="color: #9f3e07; text-decoration: underline;">
                                                            {{ $item->product_name }}
                                                        </a>
                                                    @else
                                                        {{ $item->product_name }}
                                                    @endif
                                                    @if ($item->note)
                                                        <div style="font-size: 12px; color: #57423a; font-weight: normal; margin-top: 4px;">
                                                            Notes: {{ $item->note }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td align="center" style="padding: 12px; font-size: 12px; color: #57423a;">
                                                    {{ $item->product_sku ?: '—' }}
                                                </td>
                                                <td align="right" style="padding: 12px; font-size: 14px; font-weight: 700; color: #9f3e07;">
                                                    {{ $item->quantity }} pcs
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            {{-- Message / Notes --}}
                            @if ($enquiry->message)
                                <h3 style="margin: 0 0 8px; font-size: 15px; font-weight: 700; color: #1f1b17;">
                                    Buyer Message / Request Notes:
                                </h3>
                                <div style="background-color: #f6ece5; border-left: 4px solid #9f3e07; padding: 12px 16px; border-radius: 4px; font-size: 14px; color: #1f1b17; line-height: 1.5; margin-bottom: 28px;">
                                    {{ $enquiry->message }}
                                </div>
                            @endif

                            {{-- Quick Action Buttons --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 8px;">
                                                    <a href="{{ $buyerWhatsAppUrl }}" target="_blank" style="display: inline-block; background-color: #2c694e; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 12px 20px; border-radius: 8px;">
                                                        Chat Buyer on WhatsApp &rarr;
                                                    </a>
                                                </td>
                                                <td style="padding-left: 8px;">
                                                    <a href="{{ $adminUrl }}" target="_blank" style="display: inline-block; background-color: #f6ece5; color: #1f1b17; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 20px; border-radius: 8px; border: 1px solid #dec0b5;">
                                                        Open in Admin Panel
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f6ece5; padding: 16px 32px; text-align: center; border-top: 1px solid #dec0b5; font-size: 12px; color: #57423a;">
                            {{ config('abatradehub.name') }} &bull; {{ config('abatradehub.address') }} &bull; Sourcing Desk
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

