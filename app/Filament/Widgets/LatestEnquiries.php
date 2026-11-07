<?php

namespace App\Filament\Widgets;

use App\Enums\EnquiryStatus;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'New enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->with(['items.product.media'])->where('status', EnquiryStatus::New)->latest()->limit(8))
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Photo')
                    ->state(fn (Enquiry $record) => $record->first_product_image)
                    ->square()
                    ->size(40)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover border border-gray-200']),

                TextColumn::make('reference')->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->state(fn (Enquiry $record) => $record->type_label)
                    ->color(fn (string $state): string => match ($state) {
                        'Product Enquiry' => 'primary',
                        'WhatsApp' => 'success',
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
                    ->description(fn (Enquiry $record) => $record->items->isEmpty() && $record->message ? Str::limit($record->message, 45) : null)
                    ->wrap(),

                TextColumn::make('name')
                    ->description(fn (Enquiry $record) => $record->company),

                TextColumn::make('phone'),

                TextColumn::make('country')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->url(fn (Enquiry $record) => EnquiryResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No new enquiries');
    }
}
