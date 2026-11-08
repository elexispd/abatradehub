<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Services\SalesAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PaymentMethodsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 4,
        'xl' => 4,
    ];

    protected ?string $heading = 'Payment Methods Settlement';

    protected ?string $description = 'Breakdown of actual cash inflows across payment channels';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);

        return $service->getPaymentMethodChartData($this->pageFilters);
    }
}
