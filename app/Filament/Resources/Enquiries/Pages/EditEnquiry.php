<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Enums\EnquiryStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnquiry extends EditRecord
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert_to_sale')
                ->label('Create Sale / Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn () => auth()->user()?->can('Create:Sale'))
                ->action(function () {
                    $enquiry = $this->record;
                    $sale = Sale::create([
                        'enquiry_id' => $enquiry->id,
                        'customer_name' => $enquiry->name,
                        'customer_phone' => $enquiry->phone,
                        'customer_email' => $enquiry->email,
                        'customer_company' => $enquiry->company,
                        'destination_country' => $enquiry->country ?: 'Nigeria',
                        'destination_city' => $enquiry->city,
                        'order_date' => now()->toDateString(),
                        'currency' => 'NGN',
                        'status' => SaleStatus::PendingPayment,
                        'payment_status' => PaymentStatus::Unpaid,
                        'customer_notes' => $enquiry->message,
                    ]);

                    foreach ($enquiry->items as $item) {
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
                    $enquiry->update(['status' => EnquiryStatus::Won]);

                    return redirect()->to(SaleResource::getUrl('edit', ['record' => $sale]));
                }),

            DeleteAction::make(),
        ];
    }
}
