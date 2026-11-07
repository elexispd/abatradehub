<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function afterCreate(): void
    {
        // First recalculate totals from created line items
        $this->record->recalculateTotals(save: true);

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

            $this->record->recalculateTotals(save: true);
        }
    }
}
