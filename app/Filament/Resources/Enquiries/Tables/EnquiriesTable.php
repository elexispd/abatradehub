<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->copyable()->weight('bold'),
                TextColumn::make('name')->searchable()->description(fn (Enquiry $record) => $record->company),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('country')->toggleable(),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('source')->badge()->color('gray')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->label('Received')->since()->dateTimeTooltip()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(EnquiryStatus::class)->multiple(),
                SelectFilter::make('source')->options(['form' => 'Website form', 'whatsapp' => 'WhatsApp']),
            ])
            ->recordActions([
                Action::make('contacted')
                    ->label('Mark contacted')
                    ->icon('heroicon-o-phone')
                    ->visible(fn (Enquiry $record) => $record->status === EnquiryStatus::New)
                    ->action(fn (Enquiry $record) => $record->update(['status' => EnquiryStatus::Contacted, 'contacted_at' => now()])),
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