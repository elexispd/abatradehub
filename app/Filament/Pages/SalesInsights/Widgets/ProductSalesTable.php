<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Models\SaleItem;
use App\Services\ProductSalesAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class ProductSalesTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Products Sold in Selected Period';

    public function table(Table $table): Table
    {
        return $table
            ->defaultKeySort(false)
            ->defaultSort('total_quantity', 'desc')
            ->query(app(ProductSalesAnalyticsService::class)->byProductQuery($this->pageFilters))
            ->columns([
                TextColumn::make('item_name')->label('Product')->weight('bold')->searchable(query: fn ($query, string $search) => $query->where('sale_items.item_name', 'like', "%{$search}%"))
                    ->description(fn (SaleItem $record) => $record->item_sku ? 'SKU: ' . $record->item_sku : null),
                TextColumn::make('category_name')->label('Category')->placeholder('Uncategorised'),
                TextColumn::make('supplier_name')->label('Supplier')->placeholder('No supplier'),
                TextColumn::make('total_quantity')->label('Units Sold')->numeric()->sortable()->alignEnd()->badge()->color('info'),
                TextColumn::make('orders_count')->label('Orders')->numeric()->sortable()->alignEnd(),
                TextColumn::make('total_revenue')->label('Revenue')->money('NGN')->sortable()->weight('bold')->color('primary')->alignEnd(),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No products sold in this period')
            ->emptyStateDescription('Try a different period or date range.');
    }
}
