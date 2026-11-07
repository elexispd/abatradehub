<?php

namespace App\Filament\Pages\SalesAnalytics\Widgets;

use App\Models\SalePayment;
use App\Services\SalesAnalyticsService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class RecentPaymentsTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 8;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Cash Inflows & Settlements';

    public function table(Table $table): Table
    {
        /** @var SalesAnalyticsService $service */
        $service = app(SalesAnalyticsService::class);
        $range = $service->resolveDateRange($this->pageFilters);

        return $table
            ->query(
                $service->getPaymentsQuery($this->pageFilters)
                    ->with(['sale', 'recorder'])
                    ->latest('payment_date')
            )
            ->columns([
                TextColumn::make('sale.invoice_number')
                    ->label('Invoice #')
                    ->weight('bold')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('sale.customer_name')
                    ->label('Customer')
                    ->description(fn (SalePayment $record) => $record->sale?->customer_phone),

                TextColumn::make('amount')
                    ->label('Amount Received')
                    ->money('NGN')
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'pos' => 'POS / Card',
                        'mobile_money' => 'Mobile Money',
                        'escrow' => 'Escrow',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'primary',
                        'pos' => 'info',
                        'cash' => 'warning',
                        'mobile_money' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('reference')
                    ->label('Session ID / Teller')
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->placeholder('System')
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('view_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (SalePayment $record) => $record->sale ? route('filament.admin.sales.invoice', ['sale' => $record->sale]) : null)
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No payments recorded in this period');
    }
}
