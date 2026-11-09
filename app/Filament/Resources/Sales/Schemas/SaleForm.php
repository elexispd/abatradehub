<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        $countries = config('abatradehub.countries', ['Nigeria']);
        $countryOptions = array_combine($countries, $countries);

        return $schema
            ->components([
                // 1. Order & Customer Information
                Section::make('Order & Customer Details')
                    ->description('General invoice details, destination, and buyer contact information.')
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->default(fn () => Sale::generateInvoiceNumber())
                            ->required()
                            ->readOnly()
                            ->prefix('#')
                            ->helperText('Standard format: ATH-INV-YYYY-XXXX'),

                        DatePicker::make('order_date')
                            ->label('Order Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Payment Due Date')
                            ->nullable(),

                        Select::make('status')
                            ->label('Order Status')
                            ->options(SaleStatus::class)
                            ->default(SaleStatus::PendingPayment->value)
                            ->required()
                            ->disabled(fn (?Sale $record) => $record && $record->isCompleted() && ! (auth()->user()?->isSuperAdmin() ?? false))
                            ->helperText(function (?Sale $record) {
                                if (! $record || ! $record->isCompleted()) {
                                    return null;
                                }

                                return auth()->user()?->isSuperAdmin()
                                    ? 'Super Admin notice: Reversing a completed order will be recorded in the audit trail.'
                                    : 'Order is completed. Only a Super Admin can reverse this status.';
                            }),

                        Select::make('destination_country')
                            ->label('Destination Country')
                            ->options($countryOptions)
                            ->default('Nigeria')
                            ->required(),

                        TextInput::make('destination_city')
                            ->label('Destination City / State')
                            ->placeholder('e.g. Accra, Ibadan, Cotonou, Onitsha')
                            ->nullable(),

                        TextInput::make('customer_name')
                            ->label('Customer / Buyer Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('customer_phone')
                            ->label('Phone Number (WhatsApp)')
                            ->tel()
                            ->required(),

                        TextInput::make('customer_company')
                            ->label('Business / Boutique Name')
                            ->placeholder('e.g. Divine Grace Boutique')
                            ->nullable(),

                        TextInput::make('customer_email')
                            ->label('Email Address')
                            ->email()
                            ->nullable(),

                        Textarea::make('shipping_address')
                            ->label('Delivery Address / Transport Park Booking')
                            ->placeholder('e.g. Shop 14 Bodija Market, Ibadan or Waybill to Onitsha Park Aba')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // 2. Products & Order Line Items (Spacious & Modern)
                Section::make('Products & Line Items')
                    ->description('Select catalog products with instant photo & price lookup, or enter custom sourced items from Aba markets.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->itemLabel(function (array $state): ?string {
                                $name = $state['item_name'] ?? null;
                                if (! $name) {
                                    return 'New Item';
                                }
                                $qty = (int) ($state['quantity'] ?? 1);
                                $unitPrice = (float) ($state['unit_price'] ?? 0);
                                $total = $qty * $unitPrice;

                                return "{$name} — Qty: {$qty} (₦" . number_format($total, 2) . ")";
                            })
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(1)
                            ->addActionLabel('+ Add Item to Sale')
                            ->live()
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 2])->schema([
                                    Select::make('product_id')
                                        ->label('Select from Catalog')
                                        ->relationship('product', 'name')
                                        ->searchable(['name', 'sku'])
                                        ->preload()
                                        ->getOptionLabelFromRecordUsing(fn (Product $record) => "{$record->name} ({$record->sku}) - ₦" . number_format($record->base_price ?: 0, 0))
                                        ->placeholder('Search catalog by name or SKU...')
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            if (! $state) {
                                                return;
                                            }
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('item_name', $product->name);
                                                $set('item_sku', $product->sku);
                                                $set('unit_price', (float) ($product->base_price ?: 0));
                                            }
                                        }),

                                    Placeholder::make('product_preview')
                                        ->label('Catalog Lookup & Stock Preview')
                                        ->content(function (Get $get) {
                                            $productId = $get('product_id');
                                            if (! $productId) {
                                                return new HtmlString('<div class="flex items-center gap-2 text-xs text-gray-400 py-2"><span class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-400 font-mono text-[10px]">Aba</span><span>Custom or bespoke sourced item (Not linked to catalog)</span></div>');
                                            }
                                            $product = Product::with('media')->find($productId);
                                            if (! $product) {
                                                return null;
                                            }
                                            $thumb = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images');
                                            $imgHtml = $thumb
                                                ? "<img src='{$thumb}' class='w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0 shadow-xs' />"
                                                : "<div class='w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs shrink-0 font-bold'>Aba</div>";
                                            $stockText = $product->stock_quantity !== null ? "{$product->stock_quantity} in stock" : 'Made to order';
                                            $priceText = $product->base_price ? '₦' . number_format($product->base_price, 2) : 'Custom price';

                                            return new HtmlString("
                                                <div class='flex items-center gap-3 p-2 rounded-lg bg-gray-50 border border-gray-200'>
                                                    {$imgHtml}
                                                    <div class='text-xs leading-tight'>
                                                        <p class='font-bold text-gray-900'>{$product->name}</p>
                                                        <p class='text-gray-500 font-mono text-[11px] mt-0.5'>SKU: {$product->sku} &bull; Base Price: {$priceText}</p>
                                                        <p class='text-emerald-700 font-medium text-[11px] mt-0.5'>Availability: {$stockText}</p>
                                                    </div>
                                                </div>
                                            ");
                                        }),
                                ]),

                                Grid::make(['default' => 1, 'sm' => 2, 'md' => 4])->schema([
                                    TextInput::make('item_name')
                                        ->label('Item Name / Description')
                                        ->required()
                                        ->columnSpan(['default' => 1, 'md' => 2])
                                        ->placeholder('e.g. Handmade Leather Loafers'),

                                    TextInput::make('item_sku')
                                        ->label('SKU')
                                        ->nullable()
                                        ->placeholder('e.g. LOAF-001'),

                                    TextInput::make('specifications')
                                        ->label('Variants / Sizes / Notes')
                                        ->placeholder('e.g. Size 42 (10), Size 43 (10), Black')
                                        ->nullable(),
                                ]),

                                Grid::make(['default' => 1, 'sm' => 3])->schema([
                                    TextInput::make('quantity')
                                        ->label('Quantity (Pieces / Pairs)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->live(),

                                    TextInput::make('unit_price')
                                        ->label('Unit Price (₦)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0)
                                        ->prefix('₦')
                                        ->required()
                                        ->live(),

                                    Placeholder::make('line_total_preview')
                                        ->label('Item Total')
                                        ->content(function (Get $get) {
                                            $qty = (float) ($get('quantity') ?? 1);
                                            $price = (float) ($get('unit_price') ?? 0);
                                            $total = $qty * $price;

                                            return new HtmlString("<div class='pt-2 font-bold text-lg text-primary'>₦" . number_format($total, 2) . '</div>');
                                        }),
                                ]),
                            ]),
                    ]),

                // 3. Logistics, Adjustments & Dynamic Financial Summary
                Grid::make(['default' => 1, 'lg' => 2])->schema([
                    Section::make('Logistics & Price Adjustments')
                        ->description('Configure shipping waybill fee, quality inspection, and special discounts.')
                        ->schema([
                            TextInput::make('shipping_fee')
                                ->label('Shipping & Waybill Fee (₦)')
                                ->numeric()
                                ->prefix('₦')
                                ->default(0)
                                ->helperText('Transport park waybill or cross-border logistics')
                                ->live(),

                            TextInput::make('inspection_fee')
                                ->label('Quality Inspection Fee (₦)')
                                ->numeric()
                                ->prefix('₦')
                                ->default(0)
                                ->helperText('100% pre-shipment quality verification in Aba')
                                ->live(),

                            TextInput::make('discount_amount')
                                ->label('Discount Amount (₦)')
                                ->numeric()
                                ->prefix('₦')
                                ->default(0)
                                ->helperText('Deducted from the total order amount')
                                ->live(),
                        ]),

                    Section::make('Live Financial Summary')
                        ->description('Real-time order calculation including installments & balance.')
                        ->schema([
                            Placeholder::make('summary_box')
                                ->label('')
                                ->content(function (Get $get) {
                                    $items = (array) ($get('items') ?? []);
                                    $subtotal = 0;
                                    foreach ($items as $item) {
                                        $qty = (float) ($item['quantity'] ?? 0);
                                        $price = (float) ($item['unit_price'] ?? 0);
                                        $subtotal += ($qty * $price);
                                    }

                                    $shipping = (float) ($get('shipping_fee') ?? 0);
                                    $inspection = (float) ($get('inspection_fee') ?? 0);
                                    $discount = (float) ($get('discount_amount') ?? 0);
                                    $total = max(0, $subtotal + $shipping + $inspection - $discount);

                                    $payments = (array) ($get('payments') ?? []);
                                    $paid = 0;
                                    foreach ($payments as $payment) {
                                        $paid += (float) ($payment['amount'] ?? 0);
                                    }

                                    $paymentOption = $get('payment_option') ?? 'unpaid';
                                    if ($paymentOption === 'full') {
                                        $paid = $total;
                                    }

                                    $balance = max(0, $total - $paid);

                                    $statusBadge = match (true) {
                                        $paid >= $total && $total > 0 => '<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Fully Paid</span>',
                                        $paid > 0 => '<span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">Partially Paid</span>',
                                        default => '<span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">Unpaid</span>',
                                    };

                                    return new HtmlString("
                                        <div class='space-y-3 p-4 rounded-xl bg-gray-50 border border-gray-200 text-sm'>
                                            <div class='flex justify-between text-gray-600'>
                                                <span>Items Subtotal:</span>
                                                <span class='font-semibold text-gray-900'>₦" . number_format($subtotal, 2) . "</span>
                                            </div>
                                            <div class='flex justify-between text-gray-600'>
                                                <span>Shipping / Freight Fee:</span>
                                                <span class='font-medium text-gray-800'>+ ₦" . number_format($shipping, 2) . "</span>
                                            </div>
                                            <div class='flex justify-between text-gray-600'>
                                                <span>Quality Inspection Fee:</span>
                                                <span class='font-medium text-gray-800'>+ ₦" . number_format($inspection, 2) . "</span>
                                            </div>
                                            <div class='flex justify-between text-gray-600'>
                                                <span>Discount Applied:</span>
                                                <span class='font-medium text-red-600'>- ₦" . number_format($discount, 2) . "</span>
                                            </div>
                                            <div class='border-t-2 border-gray-200 pt-3 flex justify-between font-extrabold text-base text-gray-900'>
                                                <span>Total Order Amount:</span>
                                                <span class='text-primary text-xl font-black'>₦" . number_format($total, 2) . "</span>
                                            </div>
                                            <div class='flex justify-between font-semibold text-emerald-700 pt-1'>
                                                <span>Total Paid:</span>
                                                <span>₦" . number_format($paid, 2) . "</span>
                                            </div>
                                            <div class='flex justify-between font-black text-base p-2.5 rounded-lg " . ($balance > 0 ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200') . "'>
                                                <span>Outstanding Balance Due:</span>
                                                <span>₦" . number_format($balance, 2) . "</span>
                                            </div>
                                            <div class='pt-2 flex justify-between items-center'>
                                                <span class='text-xs font-semibold text-gray-500'>Payment Status:</span>
                                                {$statusBadge}
                                            </div>
                                        </div>
                                    ");
                                }),
                        ]),
                ]),

                // 4. Payment Settlement & Recording
                Section::make('Payment Settlement & Recording')
                    ->description(fn (?Sale $record) => ($record && ($record->payment_status === PaymentStatus::Paid || $record->balance_due <= 0))
                        ? 'This wholesale order has been settled in full. No additional installments or payment recording are required.'
                        : 'Settle payment completely at once, record installments, or generate an unpaid invoice for the customer.')
                    ->schema([
                        // Read-Only Settlement Summary when order is 100% fully paid (e.g. via Paystack or full offline payment)
                        Placeholder::make('settled_payment_summary')
                            ->label('')
                            ->visible(fn (?Sale $record) => $record && ($record->payment_status === PaymentStatus::Paid || $record->balance_due <= 0))
                            ->content(function (?Sale $record) {
                                if (! $record) {
                                    return null;
                                }

                                $payments = $record->payments;
                                $isPaystack = $payments->contains(fn ($p) => $p->payment_method === 'paystack');
                                $totalPaid = number_format($record->amount_paid, 2);
                                $currency = $record->currency ?: 'NGN';

                                $rowsHtml = '';
                                foreach ($payments as $p) {
                                    $amt = number_format($p->amount, 2);
                                    $methodLabel = \App\Models\SalePayment::methodLabel($p->payment_method);
                                    $date = $p->payment_date?->format('d M, Y') ?? '-';
                                    $ref = $p->reference ?: '-';
                                    $note = $p->notes ?: '-';
                                    $isOnline = $p->payment_method === 'paystack';
                                    $badge = $isOnline
                                        ? "<span class='px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200'>Paystack Online</span>"
                                        : "<span class='px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200'>{$methodLabel}</span>";

                                    $rowsHtml .= "
                                        <tr class='border-b border-gray-100 last:border-0 text-xs'>
                                            <td class='py-2.5 pr-3 font-bold text-gray-900'>{$currency} {$amt}</td>
                                            <td class='py-2.5 px-3'>{$badge}</td>
                                            <td class='py-2.5 px-3 text-gray-600'>{$date}</td>
                                            <td class='py-2.5 px-3 font-mono text-[11px] text-gray-700'>{$ref}</td>
                                            <td class='py-2.5 pl-3 text-gray-500'>{$note}</td>
                                        </tr>
                                    ";
                                }

                                $bannerTitle = $isPaystack ? '100% Fully Settled Online via Paystack' : 'Wholesale Order Paid in Full';
                                $bannerSubtitle = $isPaystack
                                    ? 'Online client checkout verified and received in full. No installments or manual adjustments needed.'
                                    : 'All wholesale items have been settled. No balance outstanding.';

                                return new HtmlString("
                                    <div class='space-y-3'>
                                        <div class='p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between flex-wrap gap-3'>
                                            <div class='flex items-center gap-3'>
                                                <div class='flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-base shadow-xs'>✓</div>
                                                <div>
                                                    <h4 class='text-sm font-bold text-emerald-950'>{$bannerTitle}</h4>
                                                    <p class='text-xs text-emerald-700 mt-0.5'>{$bannerSubtitle}</p>
                                                </div>
                                            </div>
                                            <div class='text-right'>
                                                <span class='text-[10px] uppercase font-bold text-emerald-800 tracking-wider block'>Total Settled</span>
                                                <span class='text-base font-extrabold text-emerald-900'>{$currency} {$totalPaid}</span>
                                            </div>
                                        </div>

                                        <div class='overflow-x-auto rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs'>
                                            <table class='w-full text-left'>
                                                <thead>
                                                    <tr class='border-b border-gray-200 text-[10px] font-bold uppercase text-gray-400'>
                                                        <th class='pb-2 pr-3'>Amount</th>
                                                        <th class='pb-2 px-3'>Payment Channel</th>
                                                        <th class='pb-2 px-3'>Payment Date</th>
                                                        <th class='pb-2 px-3'>Reference / Session</th>
                                                        <th class='pb-2 pl-3'>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {$rowsHtml}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                ");
                            }),

                        // How is this order being paid? (Visible ONLY when order has an outstanding balance)
                        Radio::make('payment_option')
                            ->label('How is this order being paid?')
                            ->visible(fn (?Sale $record) => ! ($record && ($record->payment_status === PaymentStatus::Paid || $record->balance_due <= 0)))
                            ->options([
                                'unpaid' => 'Unpaid / Pay Later (Customer will pay against generated invoice)',
                                'full' => 'Complete Payment in Full at Once (100% Settled)',
                                'installments' => 'Progressive Installments (Multiple partial payments)',
                            ])
                            ->default('unpaid')
                            ->live(),

                        // Single Full Payment fields (shown when 'full' selected and NOT fully paid)
                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 4])
                            ->visible(fn (Get $get, ?Sale $record) => ! ($record && ($record->payment_status === PaymentStatus::Paid || $record->balance_due <= 0)) && (($get('payment_option') ?? 'unpaid') === 'full'))
                            ->schema([
                                Select::make('full_payment_method')
                                    ->label('Payment Method')
                                    ->options(\App\Models\SalePayment::methodOptions())
                                    ->default('bank_transfer')
                                    ->required(fn (Get $get) => ($get('payment_option') ?? 'unpaid') === 'full'),

                                TextInput::make('full_payment_reference')
                                    ->label('Transaction Reference / Teller #')
                                    ->placeholder('e.g. Bank NIP session ID')
                                    ->nullable(),

                                DatePicker::make('full_payment_date')
                                    ->label('Payment Date')
                                    ->default(now())
                                    ->required(fn (Get $get) => ($get('payment_option') ?? 'unpaid') === 'full'),

                                TextInput::make('full_payment_note')
                                    ->label('Payment Note')
                                    ->default('Full payment completed at once')
                                    ->nullable(),
                            ]),

                        // Installment Payments Repeater (shown ONLY when 'installments' selected and order is NOT fully paid)
                        Repeater::make('payments')
                            ->relationship()
                            ->visible(fn (Get $get, ?Sale $record) => ! ($record && ($record->payment_status === PaymentStatus::Paid || $record->balance_due <= 0)) && (($get('payment_option') ?? 'unpaid') === 'installments' || ($record && $record->payments()->exists() && $record->balance_due > 0)))
                            // Once a payment is recorded / sale paid, payments cannot be deleted
                            ->deletable(fn (?Sale $record) => $record === null || ($record->payment_status !== PaymentStatus::Paid && $record->amount_paid <= 0))
                            ->itemLabel(function (array $state): ?string {
                                $amount = (float) ($state['amount'] ?? 0);
                                $date = $state['payment_date'] ?? 'Date unrecorded';
                                $method = \App\Models\SalePayment::methodLabel($state['payment_method'] ?? 'bank_transfer');

                                return "₦" . number_format($amount, 2) . " ({$method}) on {$date}";
                            })
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('+ Add Another Installment Payment')
                            ->live()
                            ->schema([
                                Grid::make(['default' => 1, 'sm' => 2, 'md' => 4])->schema([
                                    DatePicker::make('payment_date')
                                        ->label('Payment Date')
                                        ->default(now())
                                        ->required(),

                                    TextInput::make('amount')
                                        ->label('Amount Paid (₦)')
                                        ->numeric()
                                        ->minValue(0.01)
                                        ->prefix('₦')
                                        ->required()
                                        ->live(),

                                    Select::make('payment_method')
                                        ->label('Payment Method')
                                        ->options(\App\Models\SalePayment::methodOptions())
                                        ->default('bank_transfer')
                                        ->required(),

                                    TextInput::make('reference')
                                        ->label('Transaction Reference / Teller #')
                                        ->placeholder('e.g. Bank NIP session ID')
                                        ->nullable(),
                                ]),

                                TextInput::make('notes')
                                    ->label('Installment Description / Note')
                                    ->placeholder('e.g. 1st installment (60% deposit before sourcing), 2nd installment, Final balance')
                                    ->nullable(),
                            ]),
                    ]),

                // 5. Notes & Terms Section
                Section::make('Notes & Order Terms')
                    ->description('Customer terms printed on the invoice and private internal staff notes.')
                    ->collapsible()
                    ->collapsed()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Textarea::make('customer_notes')
                            ->label('Customer Notes (Visible on Invoice)')
                            ->placeholder('e.g. Goods to be packed in export-grade cartons. Waybill receipt to be sent via WhatsApp.')
                            ->rows(3),

                        Textarea::make('internal_notes')
                            ->label('Internal Sourcing Notes (Staff Only)')
                            ->placeholder('e.g. Sourced from Chief Okoro Line 3 Ariaria. Lead time 3 days.')
                            ->rows(3),
                    ]),

                // 6. Sales Activity & Audit Trail (Requirement 3: Record all staff & client activity)
                Section::make('Sales Activity & Audit Trail')
                    ->description('Complete accountability log: who recorded the order (staff or online client), who edited it, and every status or payment change.')
                    ->collapsible()
                    ->visible(fn (?Sale $record) => $record !== null && $record->exists)
                    ->schema([
                        Placeholder::make('audit_history')
                            ->label('')
                            ->content(fn (?Sale $record) => $record ? new HtmlString(view('filament.resources.sales.audit-history', ['record' => $record])->render()) : null),
                    ]),
            ]);
    }
}
