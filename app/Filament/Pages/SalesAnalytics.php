<?php

namespace App\Filament\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Pages\SalesAnalytics\Widgets\DestinationCountryChart;
use App\Filament\Pages\SalesAnalytics\Widgets\FinancialStatsOverview;
use App\Filament\Pages\SalesAnalytics\Widgets\OrderStatusChart;
use App\Filament\Pages\SalesAnalytics\Widgets\PaymentMethodsChart;
use App\Filament\Pages\SalesAnalytics\Widgets\RecentPaymentsTable;
use App\Filament\Pages\SalesAnalytics\Widgets\RevenueTrendChart;
use App\Filament\Pages\SalesAnalytics\Widgets\StaffSalesPerformanceTable;
use App\Filament\Pages\SalesAnalytics\Widgets\TopProductsTable;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

use UnitEnum;

class SalesAnalytics extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/sales-analytics';

    protected static string | UnitEnum | null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Financial Analytics';

    protected static ?string $title = 'Sales & Financial Analytics';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedPresentationChartLine;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->can('View:SalesAnalytics')
            || $user->can('view_sales_analytics')
            || $user->hasRole(['admin', 'sales'])
            || $user->can('ViewAny:Sale')
            || $user->can('view_any_sale');
    }

    public function getColumns(): int | array
    {
        return 12;
    }

    public function filtersForm(Schema $schema): Schema
    {
        $countries = config('abatradehub.countries', ['Nigeria']);
        $countryOptions = array_combine($countries, $countries);

        return $schema
            ->columns(1)
            ->components([
                Section::make('Analytics Filters')
                    ->columnSpanFull()
                    ->description('Refine all metrics, charts, and tables below in real-time')
                    ->icon('heroicon-m-funnel')
                    ->iconColor('primary')
                    ->collapsible()
                    ->compact()
                    ->extraAttributes([
                        'class' => 'ring-1 ring-primary-200 dark:ring-primary-800 bg-gradient-to-br from-primary-50 to-white dark:from-primary-950 dark:to-gray-900 shadow-sm',
                    ])
                    ->schema([
                        Select::make('period')
                            ->label('Time Period')
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->prefixIconColor('primary')
                            ->options([
                                'this_month'   => 'This Month',
                                'last_month'   => 'Last Month',
                                'this_week'    => 'This Week',
                                'last_week'    => 'Last Week',
                                'today'        => 'Today',
                                'yesterday'    => 'Yesterday',
                                'this_quarter' => 'This Quarter',
                                'this_year'    => 'This Year',
                                'custom'       => 'Custom Date Range',
                            ])
                            ->default('this_month')
                            ->live(),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->prefixIcon('heroicon-m-arrow-right-circle')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->default(now()->startOfMonth())
                            ->maxDate(now())
                            ->live(),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->prefixIcon('heroicon-m-arrow-left-circle')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->default(now())
                            ->live(),

                        Select::make('destination_country')
                            ->label('Destination Market')
                            ->prefixIcon('heroicon-m-globe-alt')
                            ->prefixIconColor('info')
                            ->options($countryOptions)
                            ->placeholder('All Destinations')
                            ->live(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->prefixIcon('heroicon-m-credit-card')
                            ->prefixIconColor('success')
                            ->options(PaymentStatus::class)
                            ->placeholder('All Statuses')
                            ->live(),
                    ])
                    ->columns([
                        'default' => 1,
                        'sm'      => 2,
                        'lg'      => 5,
                    ]),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            FinancialStatsOverview::class,
            RevenueTrendChart::class,
            OrderStatusChart::class,
            DestinationCountryChart::class,
            PaymentMethodsChart::class,
            TopProductsTable::class,
            StaffSalesPerformanceTable::class,
            RecentPaymentsTable::class,
        ];
    }
}

