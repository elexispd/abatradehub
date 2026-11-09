<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt &bull; {{ $sale->invoice_number }} &bull; {{ config('abatradehub.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#9f3e07',
                        secondary: '#2c694e',
                        tertiary: '#7f5420',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            @page {
                size: A4;
                margin: 12mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased p-4 sm:p-8 min-h-screen flex flex-col items-center">

    {{-- Screen Action Bar (Hidden on print) --}}
    <div class="no-print w-full max-w-4xl mb-6 flex flex-wrap items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Wholesale Catalog
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('orders.track', ['invoice' => $sale->invoice_number]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary/80 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Track Shipment Live
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm text-gray-500 font-mono">{{ $sale->invoice_number }}</span>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg shadow-sm hover:bg-primary/90 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    {{-- Printable Receipt Container --}}
    <div class="receipt-container w-full max-w-4xl bg-white p-8 sm:p-12 rounded-2xl shadow-md border border-gray-200 relative overflow-hidden">
        {{-- Central Diagonal Document Watermark --}}
        <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden z-0" aria-hidden="true">
            <div class="flex flex-col items-center justify-center rotate-[-25deg] opacity-[0.04] print:opacity-[0.06] scale-110">
                <img src="{{ asset('img/logo.png') }}" alt="" class="w-80 h-80 object-contain grayscale" />
                <span class="text-4xl sm:text-5xl font-black uppercase tracking-widest text-gray-900 mt-3 whitespace-nowrap">
                    OFFICIAL RECEIPT
                </span>
                <span class="text-sm font-extrabold uppercase tracking-widest text-gray-700 mt-1">
                    ABATRADEHUB &bull; 100% PRE-SHIPMENT INSPECTED
                </span>
            </div>
        </div>

        {{-- PAID Watermark Stamp --}}
        <div class="absolute right-6 sm:right-12 top-24 sm:top-28 border-4 border-emerald-600 text-emerald-700 font-black text-xl sm:text-2xl tracking-widest uppercase px-5 sm:px-6 py-1.5 sm:py-2 rounded-xl rotate-[-12deg] opacity-80 select-none pointer-events-none z-10 shadow-sm print:opacity-90">
            PAID VIA PAYSTACK
        </div>

        {{-- Top Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-8 border-b border-gray-200 relative z-10">
            <div>
                {{-- Logo replacing "AbaTradeHub" text --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('abatradehub.name') }}" class="h-20 w-auto object-contain rounded-xl" />
                </div>
        
                <div class="text-sm text-gray-600 mt-2 space-y-0.5">
                    <p>{{ config('abatradehub.address') }}</p>
                    <p>WhatsApp: {{ config('abatradehub.whatsapp_display') }}</p>
                    <p>Email: {{ config('abatradehub.email') }}</p>
                </div>
            </div>

            <div class="sm:text-right">
                <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full mb-2">
                    Official Payment Receipt
                </span>
                <h1 class="text-3xl font-extrabold text-gray-900 font-mono tracking-tight">{{ $sale->invoice_number }}</h1>
                <div class="text-sm text-gray-600 mt-2 space-y-0.5">
                    <p>Order Date: <span class="font-semibold text-gray-900">{{ $sale->order_date?->format('F j, Y') ?? now()->format('F j, Y') }}</span></p>
                    <p>Paid At: <span class="font-semibold text-green-700">{{ $sale->paid_at?->format('F j, Y g:i A') ?? now()->format('F j, Y') }}</span></p>
                </div>
            </div>
        </div>

        {{-- Billed To & Shipping Destination --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 border-b border-gray-200 text-sm relative z-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block mb-2">Customer / Buyer</span>
                <p class="text-base font-bold text-gray-900">{{ $sale->customer_name }}</p>
                <p class="text-gray-600 mt-1">Phone: {{ $sale->customer_phone }}</p>
                @if ($sale->customer_email)
                    <p class="text-gray-600">Email: {{ $sale->customer_email }}</p>
                @endif
            </div>

            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block mb-2">Delivery &amp; Waybill Destination</span>
                <p class="font-semibold text-gray-900">{{ $sale->destination_city }}, {{ $sale->destination_country }}</p>
                @if ($sale->shipping_address)
                    <p class="text-gray-600 mt-0.5">{{ $sale->shipping_address }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-1">Fulfillment: Dispatched from Aba Commercial Parks</p>
            </div>
        </div>

        {{-- Order Items Table --}}
        <div class="py-6 relative z-10">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-300 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3 pr-4">#</th>
                        <th class="py-3 px-4">Item &amp; Specifications</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Unit Rate</th>
                        <th class="py-3 pl-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($sale->items as $index => $item)
                        <tr>
                            <td class="py-4 pr-4 font-mono text-xs text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-gray-900">{{ $item->item_name }}</p>
                                @if ($item->item_sku)
                                    <p class="text-xs font-mono text-gray-500">SKU: {{ $item->item_sku }}</p>
                                @endif
                                @if ($item->specifications)
                                    <p class="text-xs text-secondary mt-1">Note: {{ $item->specifications }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-gray-900">{{ $item->quantity }}</td>
                            <td class="py-4 px-4 text-right text-gray-700">₦{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 pl-4 text-right font-bold text-gray-900">₦{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Financial Totals & Payments --}}
        <div class="pt-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-start gap-6 relative z-10">
            <div class="text-sm space-y-2 max-w-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block">Payment Information</span>
                @foreach ($sale->payments as $payment)
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                            <span>Method: {{ strtoupper($payment->payment_method) }}</span>
                            <span>{{ $payment->payment_date?->format('M j, Y') }}</span>
                        </div>
                        <div class="mt-1 font-mono text-xs text-gray-500 truncate" title="{{ $payment->reference }}">
                            Ref: {{ $payment->reference }}
                        </div>
                        <div class="mt-1 font-bold text-green-700">
                            Amount Paid: ₦{{ number_format($payment->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-gray-900">₦{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Quality Inspection:</span>
                    <span class="font-semibold text-green-700">FREE</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Shipping / Waybill:</span>
                    <span class="font-semibold text-gray-900">Waybill Paid / Arranged</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-300 text-base font-extrabold text-gray-900">
                    <span>Total Paid:</span>
                    <span class="text-primary text-lg">₦{{ number_format($sale->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs font-semibold text-green-700">
                    <span>Balance Due:</span>
                    <span>₦0.00</span>
                </div>
            </div>
        </div>

        {{-- Footer Note --}}
        <div class="mt-10 pt-6 border-t border-gray-200 text-center text-xs text-gray-500 space-y-1 relative z-10">
            <p class="font-semibold text-gray-700">Thank you for sourcing with {{ config('abatradehub.name') }}!</p>
            <p>All items undergo strict 100% seam and material inspection in Aba prior to dispatch.</p>
            <p>For questions or waybill park tracking, contact our WhatsApp desk: {{ config('abatradehub.whatsapp_display') }}</p>
        </div>
    </div>
</body>
</html>

