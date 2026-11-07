<?php

namespace App\Filament\Widgets;

use App\Enums\EnquiryStatus;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'New enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->where('status', EnquiryStatus::New)->latest()->limit(8))
            ->columns([
                TextColumn::make('reference')->weight('bold'),
                TextColumn::make('name'),
                TextColumn::make('phone'),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->url(fn (Enquiry $record) => EnquiryResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No new enquiries');
    }
}