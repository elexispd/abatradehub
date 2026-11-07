<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockProducts extends TableWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low stock';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->lowStock()->with('media')->orderBy('stock_quantity')->limit(8))
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')->label('')->collection('images')->conversion('thumb')->limit(1)->square()->size(40),
                TextColumn::make('name')->description(fn (Product $record) => $record->sku),
                TextColumn::make('stock_quantity')->label('In stock')->color('danger')->weight('bold'),
                TextColumn::make('low_stock_threshold')->label('Threshold'),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('edit')->label('Edit')->url(fn (Product $record) => ProductResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nothing is running low');
    }
}