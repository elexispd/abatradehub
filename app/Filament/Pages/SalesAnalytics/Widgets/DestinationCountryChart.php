<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Services\SalesAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DestinationCountryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 4,
        'xl' => 4,
    ];

    protected ?string $heading = 'Geographic Market Distribution';

    protected ?string $description = 'Domestic Nigeria trade vs international African wholesale exports';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);

        return $service->getDestinationCountryChartData($this->pageFilters);
    }
}
