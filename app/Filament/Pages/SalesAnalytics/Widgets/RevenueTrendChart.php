<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Services\SalesAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Revenue & Cash Inflow Trend';

    protected ?string $description = 'Invoiced order values vs actual payments received over the selected period';

    protected ?string $maxHeight = '360px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);

        return $service->getRevenueTrendChartData($this->pageFilters);
    }
}
