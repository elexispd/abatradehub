<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SalePayment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        $countries = config('abatradehub.countries', ['Nigeria']);
        $countryOptions = array_combine($countries, $countries);

        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Sale $record) => $record->customer_company ?: $record->customer_phone),

                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('destination_country')
                    ->label('Destination')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn (Sale $record) => $record->currency)
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money(fn (Sale $record) => $record->currency)
                    ->color('success')
                    ->sortable(),

                TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money(fn (Sale $record) => $record->currency)
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->weight(fn ($state) => $state > 0 ? 'bold' : null)
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Recorded By')
                    ->default(fn (Sale $record) => $record->customer_name ? "Online: {$record->customer_name}" : 'Online Order')
                    ->badge(fn (Sale $record) => $record->created_by === null)
                    ->color(fn (Sale $record) => $record->created_by === null ? 'info' : 'gray')
                    ->description(fn (Sale $record) => $record->created_at?->format('d M, Y h:i A'))
                    ->toggleable(),

                TextColumn::make('editor.name')
                    ->label('Last Edited By')
                    ->default(fn (Sale $record) => $record->created_by === null ? ($record->payment_status === PaymentStatus::Paid ? 'Paystack Gateway' : 'Online System') : '-')
                    ->description(fn (Sale $record) => $record->updated_at?->diffForHumans())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Order Status')
                    ->options(SaleStatus::class),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(PaymentStatus::class),

                SelectFilter::make('destination_country')
                    ->label('Country')
                    ->options($countryOptions),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Sale $record) => route('filament.admin.sales.invoice', ['sale' => $record]))
                    ->openUrlInNewTab(),

                Action::make('complete_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Sale $record) => $record->balance_due > 0)
                    ->form([
                        Radio::make('payment_mode')
                            ->label('Payment Type')
                            ->options([
                                'full' => 'Complete in Full (100% of remaining balance)',
                                'partial' => 'Progressive Installment (Partial amount)',
                            ])
                            ->default('full')
                            ->live(),

                        TextInput::make('amount')
                            ->label('Payment Amount (₦)')
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('₦')
                            ->default(fn (Sale $record) => $record->balance_due)
                            ->disabled(fn ($get) => ($get('payment_mode') ?? 'full') === 'full')
                            ->dehydrated()
                            ->helperText(fn (Sale $record) => 'Outstanding balance: ₦' . number_format($record->balance_due, 2))
                            ->required(),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(\App\Models\SalePayment::methodOptions())
                            ->default('bank_transfer')
                            ->required(),

                        TextInput::make('reference')
                            ->label('Transaction Reference / Teller #')
                            ->placeholder('e.g. Bank NIP session ID')
                            ->nullable(),

                        TextInput::make('notes')
                            ->label('Payment Note')
                            ->placeholder('e.g. Full settlement at once, or 2nd installment')
                            ->default(fn ($get) => ($get('payment_mode') ?? 'full') === 'full' ? 'Full payment completed at once' : 'Installment payment')
                            ->nullable(),
                    ])
                    ->action(function (Sale $record, array $data): void {
                        $payAmount = ($data['payment_mode'] ?? 'full') === 'full'
                            ? $record->balance_due
                            : min((float) $data['amount'], (float) $record->balance_due);

                        $record->payments()->create([
                            'amount' => $payAmount,
                            'payment_date' => $data['payment_date'],
                            'payment_method' => $data['payment_method'],
                            'reference' => $data['reference'] ?? null,
                            'notes' => $data['notes'] ?? (($data['payment_mode'] ?? 'full') === 'full' ? 'Full payment completed at once' : 'Installment payment'),
                            'recorded_by' => auth()->id(),
                        ]);

                        $record->recalculateTotals(save: true);

                        $fresh = $record->fresh();
                        Notification::make()
                            ->title($fresh->payment_status === PaymentStatus::Paid ? 'Sale Fully Paid' : 'Payment Recorded')
                            ->body('Payment of ₦' . number_format($payAmount, 2) . ' recorded. Remaining balance: ₦' . number_format($fresh->balance_due, 2))
                            ->success()
                            ->send();
                    }),

                Action::make('mark_completed')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Sale $record) => ! $record->isCompleted())
                    ->requiresConfirmation()
                    ->modalHeading('Mark Order as Completed')
                    ->modalDescription('Once marked completed, this order is locked and only a Super Admin can reverse it.')
                    ->action(function (Sale $record): void {
                        $record->status = SaleStatus::Completed;
                        $record->save();

                        Notification::make()
                            ->title('Order Completed')
                            ->body("Order {$record->invoice_number} marked as completed.")
                            ->success()
                            ->send();
                    }),

                Action::make('reverse_order')
                    ->label('Reverse Status')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Sale $record) => $record->isCompleted() && (auth()->user()?->isSuperAdmin() ?? false))
                    ->modalHeading('Super Admin: Reverse Completed Order')
                    ->modalDescription('Reversing a completed order will unlock it and record a dedicated security audit log.')
                    ->form([
                        Select::make('new_status')
                            ->label('New Order Status')
                            ->options([
                                SaleStatus::Processing->value => 'Processing / Sourcing',
                                SaleStatus::Inspection->value => 'Quality Inspection',
                                SaleStatus::Dispatched->value => 'Dispatched',
                                SaleStatus::PendingPayment->value => 'Pending Payment',
                                SaleStatus::Cancelled->value => 'Cancelled',
                            ])
                            ->default(SaleStatus::Processing->value)
                            ->required(),

                        TextInput::make('reason')
                            ->label('Reason for Reversal')
                            ->placeholder('e.g. Order return or customer requested cancellation')
                            ->required(),
                    ])
                    ->action(function (Sale $record, array $data): void {
                        $record->reversal_reason = $data['reason'];
                        $record->status = $data['new_status'];
                        $record->save();

                        Notification::make()
                            ->title('Order Status Reversed')
                            ->body("Order {$record->invoice_number} reversed to {$data['new_status']} and logged.")
                            ->warning()
                            ->send();
                    }),

                EditAction::make(),

                // A paid sale or sale with payments cannot be deleted!
                DeleteAction::make()
                    ->visible(fn (Sale $record) => $record->payment_status !== PaymentStatus::Paid && $record->amount_paid <= 0),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            // Filter out paid sales or sales with payments
                            $deletable = $records->filter(fn (Sale $r) => $r->payment_status !== PaymentStatus::Paid && $r->amount_paid <= 0);
                            $skippedCount = $records->count() - $deletable->count();

                            $deletable->each->delete();

                            if ($skippedCount > 0) {
                                Notification::make()
                                    ->title('Protected sales skipped')
                                    ->body("{$skippedCount} sale(s) with recorded payments could not be deleted.")
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
