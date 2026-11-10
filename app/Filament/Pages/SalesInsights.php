<?php

namespace App\Filament\Pages;

use App\Filament\Pages\SalesInsights\Widgets\CategorySalesTable;
use App\Filament\Pages\SalesInsights\Widgets\ProductSalesTable;
use App\Filament\Pages\SalesInsights\Widgets\SalesInsightsStats;
use App\Filament\Pages\SalesInsights\Widgets\SupplierSalesTable;
use App\Filament\Pages\SalesInsights\Widgets\UnitsSoldTrendChart;
use App\Models\Category;
use App\Models\Supplier;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SalesInsights extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/sales-insights';

    protected static string | UnitEnum | null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Sales Analytics';

    protected static ?string $title = 'Sales Analytics';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedChartBar;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->can('View:SalesInsights'));
    }

    public function getColumns(): int | array
    {
        return 12;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Period & Filters')
                ->icon(Heroicon::OutlinedFunnel)
                ->columnSpanFull()
                ->description('What sold, by category, product and supplier, for any period or custom date range')
                ->schema([
                    Select::make('period')
                        ->label('Time Period')
                        ->options([
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'this_week' => 'This Week',
                            'last_week' => 'Last Week',
                            'this_month' => 'This Month',
                            'last_month' => 'Last Month',
                            'this_quarter' => 'This Quarter',
                            'this_year' => 'This Year',
                            'custom' => 'Custom Date Range',
                        ])
                        ->default('this_month')
                        ->live(),
                    DatePicker::make('start_date')
                        ->label('From')
                        ->visible(fn (Get $get) => $get('period') === 'custom')
                        ->default(now()->startOfMonth())
                        ->live(),
                    DatePicker::make('end_date')
                        ->label('To')
                        ->visible(fn (Get $get) => $get('period') === 'custom')
                        ->default(now())
                        ->live(),
                    Select::make('category_id')
                        ->label('Category')
                        ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('All categories')
                        ->live(),
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('All suppliers')
                        ->live(),
                ])
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 5]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            SalesInsightsStats::class,
            UnitsSoldTrendChart::class,
            CategorySalesTable::class,
            SupplierSalesTable::class,
            ProductSalesTable::class,
        ];
    }
}

