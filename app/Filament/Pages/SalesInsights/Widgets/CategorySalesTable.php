<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Services\ProductSalesAnalyticsService;
use Illuminate\Database\Eloquent\Builder;

class CategorySalesTable extends GroupedSalesTable
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Sales by Category';

    protected function groupLabel(): string
    {
        return 'Category';
    }

    protected function groupedQuery(ProductSalesAnalyticsService $service): Builder
    {
        return $service->byCategoryQuery($this->pageFilters);
    }
}
