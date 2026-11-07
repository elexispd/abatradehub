<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\EnquiryItem;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnquiryItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'enquiryItems';

    protected static ?string $title = 'Customer Enquiries';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->heading('Customer Enquiries for this Product')
            ->description('All wholesale enquiries received from buyers specifically for this item.')
            ->columns([
                TextColumn::make('enquiry.reference')
                    ->label('Reference')
                    ->weight('bold')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('enquiry.name')
                    ->label('Customer')
                    ->description(fn (EnquiryItem $record) => $record->enquiry?->company)
                    ->searchable(),
                TextColumn::make('enquiry.phone')
                    ->label('Phone')
                    ->copyable()
                    ->url(fn (EnquiryItem $record) => $record->enquiry?->phone ? 'tel:'.$record->enquiry->phone : null),
                TextColumn::make('enquiry.country')
                    ->label('Country')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('quantity')
                    ->label('Qty (pcs)')
                    ->weight('bold'),
                TextColumn::make('enquiry.status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Buyer Note')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('view_enquiry')
                    ->label('Open enquiry')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (EnquiryItem $record) => $record->enquiry_id ? EnquiryResource::getUrl('edit', ['record' => $record->enquiry_id]) : null),
            ]);
    }
}
