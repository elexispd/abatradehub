<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Services\ProductSalesAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesInsightsStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $s = app(ProductSalesAnalyticsService::class)->summary($this->pageFilters);

        return [
            Stat::make('Units Sold', number_format($s['units']))->description($s['label'])->color('info'),
            Stat::make('Item Revenue', "\u{20A6}" . number_format($s['revenue'], 2))->description($s['orders'] . ' orders')->color('primary'),
            Stat::make('Distinct Products Sold', number_format($s['products']))->description('Cancelled orders excluded')->color('success'),
        ];
    }
}
