<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Models\Sale;
use App\Services\SalesAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class StaffSalesPerformanceTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected static ?string $heading = 'Staff Sales Performance';

    public function table(Table $table): Table
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);
        $range = $service->resolveDateRange($this->pageFilters);

        return $table
            ->defaultKeySort(false)
            ->defaultSort('gross_sales', 'desc')
            ->query(
                $service->getSalesQuery($this->pageFilters)
                    ->selectRaw('MIN(id) as id, created_by, COUNT(id) as orders_count, SUM(total_amount) as gross_sales, SUM(amount_paid) as collected_amount, SUM(balance_due) as pending_balance')
                    ->groupBy('created_by')
                    ->with(['creator'])
            )
            ->columns([
                TextColumn::make('creator.name')
                    ->label('Staff Member')
                    ->default('System / Direct')
                    ->description(fn (Sale $record) => $record->creator?->email)
                    ->weight('bold'),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->alignEnd()
                    ->badge()
                    ->color('info'),

                TextColumn::make('gross_sales')
                    ->label('Gross Invoiced')
                    ->money('NGN')
                    ->weight('bold')
                    ->color('primary')
                    ->alignEnd(),

                TextColumn::make('collected_amount')
                    ->label('Cash Collected')
                    ->money('NGN')
                    ->color('success')
                    ->alignEnd(),

                TextColumn::make('pending_balance')
                    ->label('Outstanding')
                    ->money('NGN')
                    ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'gray')
                    ->alignEnd(),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No staff activity in this period');
    }
}
