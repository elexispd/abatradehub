<?php

namespace App\Filament\Pages\SalesInsights\Widgets;

use App\Services\ProductSalesAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class UnitsSoldTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Units Sold Over Time';

    protected ?string $description = 'Daily for ranges up to ~2 months, monthly beyond that';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $trend = app(ProductSalesAnalyticsService::class)->trend($this->pageFilters);

        return [
            'datasets' => [[
                'label' => 'Units sold',
                'data' => $trend->pluck('units')->map(fn ($v) => (int) $v)->values()->all(),
                'backgroundColor' => '#9f3e07',
            ]],
            'labels' => $trend->keys()->values()->all(),
        ];
    }
}
