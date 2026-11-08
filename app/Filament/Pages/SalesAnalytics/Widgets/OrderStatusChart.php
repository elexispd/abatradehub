<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Services\SalesAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OrderStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 4,
        'xl' => 4,
    ];

    protected ?string $heading = 'Order Pipeline & Status';

    protected ?string $description = 'Volume of orders by current processing and fulfillment stage';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);

        return $service->getOrderStatusChartData($this->pageFilters);
    }
}
