<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Models\SaleItem;
use App\Services\SalesAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class TopProductsTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 6,
        'xl' => 6,
    ];

    protected static ?string $heading = 'Top Selling Wholesale Products';

    public function table(Table $table): Table
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);
        $saleIds = $service->getSalesQuery($this->pageFilters)->pluck('id');

        return $table
            ->defaultKeySort(false)
            ->defaultSort('total_revenue', 'desc')
            ->query(
                SaleItem::query()
                    ->whereIn('sale_id', $saleIds)
                    ->selectRaw('MIN(id) as id, item_name, item_sku, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
                    ->groupBy('item_name', 'item_sku')
            )
            ->columns([
                TextColumn::make('item_name')
                    ->label('Product / Item')
                    ->weight('bold')
                    ->description(fn (SaleItem $record) => $record->item_sku ? 'SKU: ' . $record->item_sku : null),

                TextColumn::make('total_quantity')
                    ->label('Units Sold')
                    ->numeric()
                    ->alignEnd()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('NGN')
                    ->weight('bold')
                    ->color('primary')
                    ->alignEnd(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No products sold in this period')
            ->emptyStateDescription('Try selecting a different date range or period.');
    }
}
