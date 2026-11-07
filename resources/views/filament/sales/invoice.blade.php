<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->invoice_number }} &bull; {{ config('abatradehub.name') }}</title>
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
            }
            .invoice-container {
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
            <a href="{{ route('filament.admin.resources.sales.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Sales
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm text-gray-500 font-mono">{{ $sale->invoice_number }}</span>
        </div>

        <div class="flex items-center gap-3">
            @php
                $cleanPhone = preg_replace('/\D+/', '', $sale->customer_phone);
                $waMsg = "Hello {$sale->customer_name}, here is your invoice {$sale->invoice_number} from " . config('abatradehub.name') . ". Total: ₦" . number_format($sale->total_amount, 2) . ", Amount Paid: ₦" . number_format($sale->amount_paid, 2) . ", Balance Due: ₦" . number_format($sale->balance_due, 2) . ". Thank you!";
                $waUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waMsg);
            @endphp
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                Send via WhatsApp
            </a>

            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-white hover:bg-amber-800 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    {{-- Printable Invoice Sheet --}}
    <div class="invoice-container w-full max-w-4xl bg-white rounded-2xl shadow-lg border border-gray-200 p-8 sm:p-12">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-gray-200 pb-8">
            <div class="flex items-start gap-4">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('abatradehub.name') }}" class="h-20 w-20 object-contain rounded-lg">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ config('abatradehub.name') }}</h1>
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ config('abatradehub.tagline') }}</p>
                    <p class="text-xs text-gray-500 mt-1 max-w-xs">{{ config('abatradehub.address') }}</p>
                    <p class="text-xs text-gray-500">Phone/WhatsApp: {{ config('abatradehub.whatsapp_display') }}</p>
                    <p class="text-xs text-gray-500">Email: {{ config('abatradehub.email') }}</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mb-2 
                    @if($sale->payment_status->value === 'paid') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($sale->payment_status->value === 'partial') bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-red-100 text-red-800 border border-red-300 @endif">
                    {{ $sale->payment_status->getLabel() }}
                </span>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">INVOICE</h2>
                <p class="font-mono text-sm font-bold text-primary mt-1">{{ $sale->invoice_number }}</p>
                <div class="text-xs text-gray-500 mt-2 space-y-0.5">
                    <p><span class="font-semibold text-gray-700">Invoice Date:</span> {{ $sale->order_date?->format('d M, Y') }}</p>
                    @if($sale->due_date)
                        <p><span class="font-semibold text-gray-700">Due Date:</span> {{ $sale->due_date->format('d M, Y') }}</p>
                    @endif
                    @if($sale->creator)
                        <p><span class="font-semibold text-gray-700">Sales Rep:</span> {{ $sale->creator->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Buyer & Shipping Address Section --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 border-b border-gray-200 text-xs">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Billed To</h3>
                <p class="text-sm font-bold text-gray-900">{{ $sale->customer_name }}</p>
                @if($sale->customer_company)
                    <p class="font-semibold text-gray-700">{{ $sale->customer_company }}</p>
                @endif
                <p class="text-gray-600 mt-1">Phone: {{ $sale->customer_phone }}</p>
                @if($sale->customer_email)
                    <p class="text-gray-600">Email: {{ $sale->customer_email }}</p>
                @endif
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Ship To / Destination</h3>
                <p class="text-sm font-bold text-gray-900">
                    {{ $sale->destination_city ? $sale->destination_city . ', ' : '' }}{{ $sale->destination_country }}
                </p>
                @if($sale->shipping_address)
                    <p class="text-gray-600 mt-1 whitespace-pre-line">{{ $sale->shipping_address }}</p>
                @else
                    <p class="text-gray-400 italic mt-1">Delivery address / park to be confirmed</p>
                @endif
                <p class="text-xs font-medium text-primary mt-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>
                    Order Status: <span class="font-semibold">{{ $sale->status->getLabel() }}</span>
                </p>
            </div>
        </div>

        {{-- Line Items Table --}}
        <div class="py-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3 px-2 w-10 text-center">#</th>
                        <th class="py-3 px-2">Item Description & Specifications</th>
                        <th class="py-3 px-2 text-center w-24">SKU</th>
                        <th class="py-3 px-2 text-center w-16">Qty</th>
                        <th class="py-3 px-2 text-right w-28">Unit Price</th>
                        <th class="py-3 px-2 text-right w-32">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($sale->items as $index => $item)
                        <tr>
                            <td class="py-3 px-2 text-center text-gray-400 font-mono">{{ $index + 1 }}</td>
                            <td class="py-3 px-2">
                                <p class="font-bold text-gray-900 text-sm">{{ $item->item_name }}</p>
                                @if($item->specifications)
                                    <p class="text-gray-500 mt-0.5">{{ $item->specifications }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center text-gray-500 font-mono text-[11px]">{{ $item->item_sku ?: '-' }}</td>
                            <td class="py-3 px-2 text-center font-bold text-gray-800">{{ $item->quantity }}</td>
                            <td class="py-3 px-2 text-right text-gray-600">₦{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 px-2 text-right font-bold text-gray-900">₦{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400 italic">No items added to this sale.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totals Breakdown --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4 pb-8 border-b border-gray-200">
            <div class="w-full sm:w-1/2 space-y-4 text-xs">
                @if($sale->customer_notes)
                    <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200">
                        <span class="font-bold text-gray-700 block mb-1">Customer / Delivery Note:</span>
                        <p class="text-gray-600 whitespace-pre-line">{{ $sale->customer_notes }}</p>
                    </div>
                @endif

                {{-- Bank Payment Instructions Box --}}
                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200">
                    <h4 class="font-bold text-primary uppercase tracking-wider text-[11px] mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Official Bank Payment Details
                    </h4>
                    <div class="space-y-1 text-gray-700 text-xs">
                        <p><span class="font-semibold">Bank Name:</span> {{ config('abatradehub.bank.name') }}</p>
                        <p><span class="font-semibold">Account Name:</span> {{ config('abatradehub.bank.account_name') }}</p>
                        <p class="text-sm font-mono font-bold text-gray-900"><span class="font-sans font-semibold text-xs text-gray-700">Account Number:</span> {{ config('abatradehub.bank.account_number') }}</p>
                        <p class="text-[11px] text-gray-500 pt-1 border-t border-amber-200/60 mt-1">
                            {{ config('abatradehub.bank.instructions') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full sm:w-5/12 space-y-2 text-xs">
                <div class="flex justify-between text-gray-600 py-1">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-gray-900">₦{{ number_format($sale->subtotal, 2) }}</span>
                </div>

                @if($sale->shipping_fee > 0)
                    <div class="flex justify-between text-gray-600 py-1">
                        <span>Shipping & Delivery Fee:</span>
                        <span class="font-semibold text-gray-900">₦{{ number_format($sale->shipping_fee, 2) }}</span>
                    </div>
                @endif

                @if($sale->inspection_fee > 0)
                    <div class="flex justify-between text-gray-600 py-1">
                        <span>Quality Inspection (Aba Hub):</span>
                        <span class="font-semibold text-gray-900">₦{{ number_format($sale->inspection_fee, 2) }}</span>
                    </div>
                @endif

                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-red-600 py-1">
                        <span>Discount:</span>
                        <span class="font-semibold">-₦{{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-base font-extrabold text-gray-900 border-t-2 border-gray-200 pt-3 pb-1">
                    <span>Total Amount:</span>
                    <span class="text-primary">₦{{ number_format($sale->total_amount, 2) }}</span>
                </div>

                <div class="flex justify-between text-xs font-semibold text-emerald-700 py-1">
                    <span>Amount Paid to Date:</span>
                    <span>₦{{ number_format($sale->amount_paid, 2) }}</span>
                </div>

                <div class="flex justify-between text-sm font-extrabold {{ $sale->balance_due > 0 ? 'text-red-700 bg-red-50 p-2 rounded-lg border border-red-200' : 'text-emerald-800 bg-emerald-50 p-2 rounded-lg border border-emerald-200' }} mt-2">
                    <span>Outstanding Balance:</span>
                    <span>₦{{ number_format($sale->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Installment Payment History Section --}}
        @if($sale->payments->isNotEmpty())
            <div class="py-6 border-b border-gray-200">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Installment Payment History
                </h3>
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 font-semibold uppercase text-[10px]">
                            <th class="py-2 px-2">Payment Date</th>
                            <th class="py-2 px-2">Method</th>
                            <th class="py-2 px-2">Reference / Session</th>
                            <th class="py-2 px-2">Notes / Description</th>
                            <th class="py-2 px-2 text-right">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sale->payments as $payment)
                            <tr>
                                <td class="py-2.5 px-2 font-medium text-gray-800">{{ $payment->payment_date?->format('d M, Y') }}</td>
                                <td class="py-2.5 px-2 uppercase font-semibold text-gray-600 text-[11px]">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                <td class="py-2.5 px-2 font-mono text-[11px] text-gray-500">{{ $payment->reference ?: '-' }}</td>
                                <td class="py-2.5 px-2 text-gray-600">{{ $payment->notes ?: 'Installment payment' }}</td>
                                <td class="py-2.5 px-2 text-right font-bold text-emerald-700">₦{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Footer --}}
        <div class="pt-8 text-center text-xs text-gray-400 space-y-1">
            <p class="font-medium text-gray-500">Thank you for sourcing with {{ config('abatradehub.name') }}!</p>
            <p>Direct B2B Wholesale &bull; Ariaria, Cemetery &amp; Ekeoha Markets, Aba, Nigeria</p>
            <p class="text-[10px] text-gray-400">Computer generated invoice. For enquiries or proof of payment, contact {{ config('abatradehub.whatsapp_display') }}.</p>
        </div>

    </div>

</body>
</html>

