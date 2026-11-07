<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'media']))
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->limit(1)
                    ->square()
                    ->size(56),
                TextColumn::make('name')->searchable()->sortable()->description(fn (Product $record) => $record->sku)->wrap(),
                TextColumn::make('sku')->label('SKU')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')->sortable()->badge()->color('gray'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('moq')->label('MOQ')->numeric()->sortable(),
                TextColumn::make('base_price')->label('Price')->money(fn (Product $record) => $record->currency)->sortable()->placeholder('Tiered'),
                TextColumn::make('stock_quantity')->label('Stock')->numeric()->sortable()->placeholder('Made to order')
                    ->color(fn (Product $record) => $record->stock_quantity !== null && $record->stock_quantity <= $record->low_stock_threshold ? 'danger' : null)
                    ->weight(fn (Product $record) => $record->stock_quantity !== null && $record->stock_quantity <= $record->low_stock_threshold ? 'bold' : null),
                TextColumn::make('supplier.name')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(ProductStatus::class),
                SelectFilter::make('category')->relationship('category', 'name')->preload(),
                SelectFilter::make('supplier')->relationship('supplier', 'name')->preload(),
                TernaryFilter::make('low_stock')->label('Low stock')
                    ->queries(
                        true: fn (Builder $q) => $q->lowStock(),
                        false: fn (Builder $q) => $q->where(fn ($q) => $q->whereNull('stock_quantity')->orWhereColumn('stock_quantity', '>', 'low_stock_threshold')),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['status' => ProductStatus::Published, 'published_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['status' => ProductStatus::Draft]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}