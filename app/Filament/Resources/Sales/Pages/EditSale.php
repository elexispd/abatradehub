<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_fully_paid')
                ->label('Complete Payment in Full')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->balance_due > 0)
                ->form([
                    TextInput::make('amount')
                        ->label('Amount to Pay (₦)')
                        ->numeric()
                        ->prefix('₦')
                        ->default(fn () => $this->record->balance_due)
                        ->readOnly()
                        ->helperText(fn () => 'Full remaining balance of ₦' . number_format($this->record->balance_due, 2) . ' will be settled.'),

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
                        ->label('Transaction Reference / Session ID')
                        ->placeholder('e.g. Bank NIP session ID or teller #')
                        ->nullable(),

                    TextInput::make('notes')
                        ->label('Payment Note')
                        ->default('Full payment completed at once')
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $this->record->payments()->create([
                        'amount' => $this->record->balance_due,
                        'payment_date' => $data['payment_date'],
                        'payment_method' => $data['payment_method'],
                        'reference' => $data['reference'] ?? null,
                        'notes' => $data['notes'] ?: 'Full payment completed at once',
                        'recorded_by' => auth()->id(),
                    ]);

                    $this->record->recalculateTotals(save: true);

                    Notification::make()
                        ->title('Sale Marked as Fully Paid')
                        ->body('Payment of ₦' . number_format($this->record->fresh()->total_amount, 2) . ' recorded.')
                        ->success()
                        ->send();
                }),

            Action::make('mark_completed')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => ! $this->record->isCompleted())
                ->requiresConfirmation()
                ->modalHeading('Mark Order as Completed')
                ->modalDescription('Are you sure you want to mark this order as completed? Once completed, this order is locked and can only be reversed by a Super Admin.')
                ->action(function (): void {
                    $this->record->status = SaleStatus::Completed;
                    $this->record->save();

                    Notification::make()
                        ->title('Order Completed')
                        ->body("Order {$this->record->invoice_number} has been marked as completed.")
                        ->success()
                        ->send();
                }),

            Action::make('reverse_order')
                ->label('Reverse Completed Order')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => $this->record->isCompleted() && (auth()->user()?->isSuperAdmin() ?? false))
                ->modalHeading('Super Admin: Reverse Completed Order')
                ->modalDescription('Reversing a completed order will unlock it and record a dedicated security audit log with your credentials.')
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
                        ->placeholder('e.g. Customer requested amendment before dispatch / payment discrepancy')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->reversal_reason = $data['reason'];
                    $this->record->status = $data['new_status'];
                    $this->record->save();

                    Notification::make()
                        ->title('Order Status Reversed')
                        ->body("Order {$this->record->invoice_number} status reversed to {$data['new_status']} and logged.")
                        ->warning()
                        ->send();
                }),

            Action::make('print_invoice')
                ->label('View / Print Invoice')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('filament.admin.sales.invoice', ['sale' => $this->record]))
                ->openUrlInNewTab(),

            // Payment/record cannot be deleted once it has been paid for
            DeleteAction::make()
                ->visible(fn () => $this->record->payment_status !== PaymentStatus::Paid && $this->record->amount_paid <= 0),
        ];
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        $paymentOption = $data['payment_option'] ?? 'unpaid';

        if ($paymentOption === 'full' && $this->record->balance_due > 0) {
            $this->record->payments()->create([
                'amount' => $this->record->balance_due,
                'payment_date' => $data['full_payment_date'] ?? now()->toDateString(),
                'payment_method' => $data['full_payment_method'] ?? 'bank_transfer',
                'reference' => $data['full_payment_reference'] ?? null,
                'notes' => $data['full_payment_note'] ?: 'Full payment completed at once',
                'recorded_by' => auth()->id(),
            ]);
        }

        $this->record->recalculateTotals(save: true);
    }
}
