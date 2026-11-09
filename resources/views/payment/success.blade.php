<x-layouts.public title="Order Confirmed - {{ $sale->invoice_number }}">
    <div class="min-h-screen bg-surface py-10 sm:py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Confirmation Box --}}
            <div class="rounded-2xl bg-surface-container-lowest p-6 sm:p-10 shadow-sm border border-outline-variant/30 space-y-8">
                {{-- Header Badge & Title --}}
                <div class="text-center space-y-3">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary-container text-secondary">
                        <x-ui.icon name="check_circle" class="text-[38px]" />
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-label-sm font-bold uppercase tracking-wider">
                        <span>Payment Confirmed via Paystack</span>
                    </div>

                    <h1 class="text-headline-md sm:text-headline-lg font-bold text-on-surface">
                        Thank You! Your Wholesale Order is Being Processed
                    </h1>

                    <p class="text-body-md text-on-surface-variant max-w-lg mx-auto">
                        Your payment of <strong class="text-primary font-bold">₦{{ number_format($sale->total_amount, 2) }}</strong> has been verified. Our Aba sourcing desk is now preparing your order for quality inspection and dispatch.
                    </p>
                </div>

                {{-- Summary Meta Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-surface-container-low text-body-sm">
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Invoice Number</span>
                        <strong class="text-label-md font-bold text-on-surface">{{ $sale->invoice_number }}</strong>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Date</span>
                        <strong class="text-label-md font-bold text-on-surface">{{ now()->format('M j, Y') }}</strong>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Payment Status</span>
                        <span class="inline-flex items-center gap-1 font-bold text-secondary">
                            <x-ui.icon name="check" class="text-[15px]" />
                            <span>Fully Paid</span>
                        </span>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Paystack Ref</span>
                        <span class="font-mono text-xs text-on-surface block truncate" title="{{ $reference }}">{{ $reference }}</span>
                    </div>
                </div>

                {{-- Order Items Table --}}
                <div class="space-y-3">
                    <h2 class="text-label-lg font-bold text-on-surface uppercase tracking-wider text-xs text-on-surface-variant">
                        Purchased Wholesale Items
                    </h2>

                    <div class="overflow-hidden rounded-xl border border-outline-variant/30">
                        <table class="w-full text-left text-body-sm">
                            <thead class="bg-surface-container text-label-sm font-semibold text-on-surface">
                                <tr>
                                    <th class="px-4 py-3">Item Description</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Unit Rate</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20">
                                @foreach ($sale->items as $item)
                                    <tr class="hover:bg-surface-container-low transition-colors">
                                        <td class="px-4 py-3 font-semibold text-on-surface">
                                            <div>{{ $item->item_name }}</div>
                                            @if ($item->item_sku)
                                                <div class="text-xs text-on-surface-variant">SKU: {{ $item->item_sku }}</div>
                                            @endif
                                            @if ($item->specifications)
                                                <div class="text-xs text-secondary mt-0.5">Note: {{ $item->specifications }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-on-surface">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-on-surface-variant">
                                            ₦{{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-primary">
                                            ₦{{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-surface-container-low text-body-sm">
                                <tr>
                                    <td colspan="3" class="px-4 py-2.5 text-right font-medium text-on-surface-variant">Subtotal:</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-on-surface">₦{{ number_format($sale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right font-medium text-on-surface-variant">Pre-Shipment Inspection:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-secondary">FREE</td>
                                </tr>
                                <tr class="border-t border-outline-variant/30 font-bold">
                                    <td colspan="3" class="px-4 py-3 text-right text-on-surface text-label-md">Total Paid:</td>
                                    <td class="px-4 py-3 text-right text-primary text-headline-sm">₦{{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Delivery Destination Details --}}
                <div class="p-4 rounded-xl bg-surface-container space-y-2">
                    <h3 class="text-label-md font-bold text-on-surface flex items-center gap-1.5">
                        <x-ui.icon name="local_shipping" class="text-[18px] text-primary" />
                        <span>Delivery &amp; Waybill Information</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-body-sm">
                        <div>
                            <span class="text-on-surface-variant">Recipient:</span>
                            <span class="font-semibold text-on-surface ml-1">{{ $sale->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-on-surface-variant">Phone / WhatsApp:</span>
                            <span class="font-semibold text-on-surface ml-1">{{ $sale->customer_phone }}</span>
                        </div>
                        <div>
                            <span class="text-on-surface-variant">Destination:</span>
                            <span class="font-semibold text-on-surface ml-1">{{ $sale->destination_city }}, {{ $sale->destination_country }}</span>
                        </div>
                        @if ($sale->shipping_address)
                            <div>
                                <span class="text-on-surface-variant">Address:</span>
                                <span class="font-semibold text-on-surface ml-1">{{ $sale->shipping_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sourcing & Dispatch Timeline --}}
                <div class="border-t border-outline-variant/30 pt-6 space-y-4">
                    <h3 class="text-label-md font-bold text-on-surface">What Happens Next?</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl bg-surface-container-low p-3.5 space-y-1">
                            <span class="text-xs font-bold text-secondary uppercase">Step 1</span>
                            <h4 class="text-body-md font-bold text-on-surface">Factory Pull</h4>
                            <p class="text-body-sm text-on-surface-variant">Our sourcing desk pulls your exact requested sizes and colors from the Aba workshop.</p>
                        </div>
                        <div class="rounded-xl bg-surface-container-low p-3.5 space-y-1">
                            <span class="text-xs font-bold text-primary uppercase">Step 2</span>
                            <h4 class="text-body-md font-bold text-on-surface">Quality Check</h4>
                            <p class="text-body-sm text-on-surface-variant">Seams, zippers, and packaging are thoroughly inspected before sealing the bales.</p>
                        </div>
                        <div class="rounded-xl bg-surface-container-low p-3.5 space-y-1">
                            <span class="text-xs font-bold text-on-surface uppercase">Step 3</span>
                            <h4 class="text-body-md font-bold text-on-surface">Park Waybill</h4>
                            <p class="text-body-sm text-on-surface-variant">Dispatched via your preferred interstate transport park with tracking receipt sent to your WhatsApp.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @php
                    $waMsg = "Hello " . config('abatradehub.name') . ", I just made payment for wholesale order {$sale->invoice_number} (₦" . number_format($sale->total_amount, 2) . ").\nCustomer: {$sale->customer_name}\nDestination: {$sale->destination_city}\nPaystack Ref: {$reference}";
                    $waUrl = \App\Support\Whatsapp::url($waMsg);
                @endphp

                <div class="pt-4 flex flex-col sm:flex-row gap-3 justify-center">
                    <a
                        href="{{ route('orders.track', ['invoice' => $sale->invoice_number]) }}"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-label-lg font-bold text-on-primary shadow-md hover:bg-primary/90 transition-all active:scale-[0.99]"
                    >
                        <x-ui.icon name="local_shipping" class="text-[20px]" />
                        <span>Track Order Live</span>
                    </a>

                    <a
                        href="{{ $waUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-secondary px-6 py-3 text-label-lg font-bold text-on-secondary shadow-md hover:bg-secondary/90 transition-all active:scale-[0.99]"
                    >
                        <x-ui.icon name="chat" class="text-[20px]" />
                        <span>Confirm Order on WhatsApp</span>
                    </a>

                    <a
                        href="{{ route('payment.receipt', ['sale' => $sale->id, 'reference' => $reference]) }}"
                        target="_blank"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-surface-container px-6 py-3 text-label-lg font-bold text-on-surface hover:bg-surface-container-high transition-all"
                    >
                        <x-ui.icon name="print" class="text-[20px]" />
                        <span>Print Official Receipt</span>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-outline-variant px-5 py-3 text-label-md font-semibold text-primary hover:bg-surface-container-low transition-all"
                    >
                        <span>Back to Catalog</span>
                        <x-ui.icon name="arrow_forward" class="text-[16px]" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>

