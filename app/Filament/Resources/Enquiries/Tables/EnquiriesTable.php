<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['items.product.media']))
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Photo')
                    ->state(fn (Enquiry $record) => $record->first_product_image)
                    ->square()
                    ->size(46)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover border border-gray-200']),

                TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->state(fn (Enquiry $record) => $record->type_label)
                    ->color(fn (string $state): string => match ($state) {
                        'Product Enquiry' => 'primary',
                        'WhatsApp' => 'success',
                        'Photo Request' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('product_enquiry')
                    ->label('Product / Request')
                    ->state(function (Enquiry $record): string {
                        if ($record->items->isNotEmpty()) {
                            return $record->items->map(fn ($item) => "{$item->product_name} ({$item->quantity} pcs)")->join(', ');
                        }

                        return 'General Quote Request';
                    })
                    ->description(function (Enquiry $record): ?string {
                        if ($record->items->isEmpty() && $record->message) {
                            return Str::limit($record->message, 50);
                        }

                        if ($record->items->count() === 1 && $record->items->first()->product_sku) {
                            return 'SKU: ' . $record->items->first()->product_sku;
                        }

                        return null;
                    })
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('items', function (Builder $itemQuery) use ($search) {
                            $itemQuery->where('product_name', 'like', "%{$search}%")
                                ->orWhere('product_sku', 'like', "%{$search}%");
                        })->orWhere('message', 'like', "%{$search}%");
                    }),

                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Enquiry $record) => $record->company),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('country')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(EnquiryStatus::class)->multiple(),
                SelectFilter::make('type')
                    ->label('Enquiry Type')
                    ->options([
                        'catalog' => 'Product Enquiries (Catalog)',
                        'quote' => 'Instant Quotes (Home)',
                        'whatsapp' => 'WhatsApp',
                        'custom_photo' => 'Photo Requests',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'catalog') {
                            $query->where(fn (Builder $q) => $q->where('source', 'catalog')->orWhereHas('items'));
                        } elseif ($value === 'quote') {
                            $query->where(fn (Builder $q) => $q->where('source', 'quote_form')->orWhere(fn ($sq) => $sq->where('source', 'form')->doesntHave('items')));
                        } elseif ($value === 'whatsapp') {
                            $query->where('source', 'whatsapp');
                        } elseif ($value === 'custom_photo') {
                            $query->where('source', 'custom_photo');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('contacted')
                    ->label('Mark contacted')
                    ->icon('heroicon-o-phone')
                    ->visible(fn (Enquiry $record) => $record->status === EnquiryStatus::New)
                    ->action(fn (Enquiry $record) => $record->update(['status' => EnquiryStatus::Contacted, 'contacted_at' => now()])),
                Action::make('convert_to_sale')
                    ->label('Create Sale / Invoice')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->can('Create:Sale'))
                    ->action(function (Enquiry $record) {
                        $sale = \App\Models\Sale::create([
                            'enquiry_id' => $record->id,
                            'customer_name' => $record->name,
                            'customer_phone' => $record->phone,
                            'customer_email' => $record->email,
                            'customer_company' => $record->company,
                            'destination_country' => $record->country ?: 'Nigeria',
                            'destination_city' => $record->city,
                            'order_date' => now()->toDateString(),
                            'currency' => 'NGN',
                            'status' => \App\Enums\SaleStatus::PendingPayment,
                            'payment_status' => \App\Enums\PaymentStatus::Unpaid,
                            'customer_notes' => $record->message,
                        ]);

                        foreach ($record->items as $item) {
                            $unitPrice = (float) ($item->product?->base_price ?: 0);
                            $sale->items()->create([
                                'product_id' => $item->product_id,
                                'item_name' => $item->product_name ?: 'Custom Sourced Product',
                                'item_sku' => $item->product_sku,
                                'quantity' => $item->quantity ?: 1,
                                'unit_price' => $unitPrice,
                                'total_price' => ($item->quantity ?: 1) * $unitPrice,
                                'specifications' => $item->note,
                            ]);
                        }

                        $sale->recalculateTotals(save: true);
                        $record->update(['status' => EnquiryStatus::Won]);

                        return redirect()->to(\App\Filament\Resources\Sales\SaleResource::getUrl('edit', ['record' => $sale]));
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_contacted')
                        ->label('Mark as contacted')
                        ->icon('heroicon-o-phone')
                        ->action(fn (Collection $records) => $records->each->update(['status' => EnquiryStatus::Contacted, 'contacted_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
