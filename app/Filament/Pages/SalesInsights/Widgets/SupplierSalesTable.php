<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Services\ProductSalesAnalyticsService;
use Illuminate\Database\Eloquent\Builder;

class SupplierSalesTable extends GroupedSalesTable
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Sales by Supplier';

    protected function groupLabel(): string
    {
        return 'Supplier';
    }

    protected function groupedQuery(ProductSalesAnalyticsService $service): Builder
    {
        return $service->bySupplierQuery($this->pageFilters);
    }
}
