<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Models\SaleItem;
use App\Services\ProductSalesAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

abstract class GroupedSalesTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = ['default' => 'full', 'lg' => 6];

    abstract protected function groupedQuery(ProductSalesAnalyticsService $service): Builder;

    protected function groupLabel(): string
    {
        return 'Name';
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultKeySort(false)
            ->defaultSort('total_revenue', 'desc')
            ->query($this->groupedQuery(app(ProductSalesAnalyticsService::class)))
            ->columns([
                TextColumn::make('label')->label($this->groupLabel())->weight('bold'),
                TextColumn::make('total_quantity')->label('Units Sold')->numeric()->sortable()->alignEnd()->badge()->color('info'),
                TextColumn::make('products_count')->label('Products')->numeric()->sortable()->alignEnd(),
                TextColumn::make('orders_count')->label('Orders')->numeric()->sortable()->alignEnd(),
                TextColumn::make('total_revenue')->label('Revenue')->money('NGN')->sortable()->weight('bold')->color('primary')->alignEnd(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No sales in this period')
            ->emptyStateDescription('Try a different period or date range.');
    }
}
