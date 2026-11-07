<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Services\SalesAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int | array | null
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 3,
        ];
    }

    protected function getStats(): array
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);
        $m = $service->getFinancialMetrics($this->pageFilters);

        return [
            Stat::make('Gross Invoiced Sales', '₦' . number_format($m['gross_revenue'], 2))
                ->description($m['total_orders'] . ' orders · ' . $m['date_range_label'])
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($m['sparkline_revenue'])
                ->color('primary')
                ->extraAttributes(['class' => 'fi-stat-card-highlighted']),

            Stat::make('Net Cash Collected', '₦' . number_format($m['net_cash_collected'], 2))
                ->description($m['collection_rate'] . '% of invoiced amount received')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Outstanding Balance', '₦' . number_format($m['outstanding_balance'], 2))
                ->description($m['outstanding_balance'] > 0 ? 'Pending receivables — follow up required' : 'All orders fully settled')
                ->descriptionIcon($m['outstanding_balance'] > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($m['outstanding_balance'] > 0 ? 'danger' : 'success'),

            Stat::make('Avg. Order Value', '₦' . number_format($m['average_order_value'], 2))
                ->description('Per order ticket size across ' . $m['total_orders'] . ' orders')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Order Completion', $m['completed_orders'] . ' / ' . $m['total_orders'])
                ->description($m['completion_rate'] . '% fulfillment rate')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($m['completion_rate'] >= 80 ? 'success' : ($m['completion_rate'] >= 50 ? 'warning' : 'danger')),

            Stat::make('Value-Added Services', '₦' . number_format($m['value_added_services'], 2))
                ->description('Shipping ₦' . number_format($m['shipping_fees']) . '  ·  Inspection ₦' . number_format($m['inspection_fees']))
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
        ];
    }
}
