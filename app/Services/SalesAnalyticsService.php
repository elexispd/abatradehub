<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesAnalyticsService
{
    /**
     * Parse date range and human label from filters.
     *
     * @param  array<string, mixed>|null  $filters
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function resolveDateRange(?array $filters): array
    {
        $period = $filters['period'] ?? 'this_month';
        $now = now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Today (' . $now->format('d M, Y') . ')',
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
                'label' => 'Yesterday (' . $now->copy()->subDay()->format('d M, Y') . ')',
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
                'label' => 'This Week (' . $now->copy()->startOfWeek()->format('d M') . ' - ' . $now->copy()->endOfWeek()->format('d M, Y') . ')',
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek(),
                'label' => 'Last Week (' . $now->copy()->subWeek()->startOfWeek()->format('d M') . ' - ' . $now->copy()->subWeek()->endOfWeek()->format('d M, Y') . ')',
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
                'label' => 'Last Month (' . $now->copy()->subMonth()->format('F Y') . ')',
            ],
            'this_quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
                'label' => 'This Quarter (Q' . $now->quarter . ' ' . $now->year . ')',
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'label' => 'This Year (' . $now->year . ')',
            ],
            'custom' => [
                'start' => ! empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : $now->copy()->startOfMonth(),
                'end' => ! empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : $now->copy()->endOfDay(),
                'label' => 'Custom Period (' . (! empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->format('d M, Y') : $now->copy()->startOfMonth()->format('d M, Y')) . ' - ' . (! empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->format('d M, Y') : $now->format('d M, Y')) . ')',
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => 'This Month (' . $now->format('F Y') . ')',
            ],
        };
    }

    /**
     * Get base sales query constrained by date range and optional filters.
     */
    public function getSalesQuery(?array $filters): Builder
    {
        $range = $this->resolveDateRange($filters);

        $query = Sale::query()
            ->whereDate('order_date', '>=', $range['start']->toDateString())
            ->whereDate('order_date', '<=', $range['end']->toDateString());

        if (! empty($filters['destination_country'])) {
            $query->where('destination_country', $filters['destination_country']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query;
    }

    /**
     * Get base payments query constrained by date range and optional filters.
     */
    public function getPaymentsQuery(?array $filters): Builder
    {
        $range = $this->resolveDateRange($filters);

        $query = SalePayment::query()
            ->whereDate('payment_date', '>=', $range['start']->toDateString())
            ->whereDate('payment_date', '<=', $range['end']->toDateString());

        if (! empty($filters['destination_country'])) {
            $country = $filters['destination_country'];
            $query->whereHas('sale', fn ($q) => $q->where('destination_country', $country));
        }

        return $query;
    }

    /**
     * Calculate core financial KPI metrics.
     *
     * @return array<string, mixed>
     */
    public function getFinancialMetrics(?array $filters): array
    {
        $range = $this->resolveDateRange($filters);
        $sales = $this->getSalesQuery($filters)->get();
        $payments = $this->getPaymentsQuery($filters)->get();

        $grossRevenue = (float) $sales->sum('total_amount');
        $netCashCollected = (float) $payments->sum('amount');
        $outstandingBalance = (float) $sales->sum('balance_due');
        $shippingFees = (float) $sales->sum('shipping_fee');
        $inspectionFees = (float) $sales->sum('inspection_fee');
        $discountAmount = (float) $sales->sum('discount_amount');

        $totalOrdersCount = $sales->count();
        $completedOrdersCount = $sales->filter(fn (Sale $s) => $s->isCompleted())->count();
        $fullyPaidOrdersCount = $sales->where('payment_status', PaymentStatus::Paid)->count();

        $averageOrderValue = $totalOrdersCount > 0 ? $grossRevenue / $totalOrdersCount : 0.0;
        $completionRate = $totalOrdersCount > 0 ? round(($completedOrdersCount / $totalOrdersCount) * 100, 1) : 0.0;
        $collectionRate = $grossRevenue > 0 ? round(($netCashCollected / $grossRevenue) * 100, 1) : 0.0;

        // Generate sparkline values (7 segments across the period)
        $sparklinePoints = $this->calculateSparklinePoints($sales, $range['start'], $range['end']);

        return [
            'gross_revenue' => $grossRevenue,
            'net_cash_collected' => $netCashCollected,
            'outstanding_balance' => $outstandingBalance,
            'shipping_fees' => $shippingFees,
            'inspection_fees' => $inspectionFees,
            'discount_amount' => $discountAmount,
            'value_added_services' => $shippingFees + $inspectionFees,
            'total_orders' => $totalOrdersCount,
            'completed_orders' => $completedOrdersCount,
            'fully_paid_orders' => $fullyPaidOrdersCount,
            'average_order_value' => $averageOrderValue,
            'completion_rate' => $completionRate,
            'collection_rate' => $collectionRate,
            'sparkline_revenue' => $sparklinePoints,
            'date_range_label' => $range['label'],
        ];
    }

    /**
     * Compute revenue & payment collection trend chart datasets.
     */
    public function getRevenueTrendChartData(?array $filters): array
    {
        $range = $this->resolveDateRange($filters);
        $startDate = $range['start']->copy();
        $endDate = $range['end']->copy();

        $sales = $this->getSalesQuery($filters)->get();
        $payments = $this->getPaymentsQuery($filters)->get();

        $daysDiff = $startDate->diffInDays($endDate);

        $labels = [];
        $revenueData = [];
        $paymentData = [];

        if ($daysDiff <= 35) {
            // Daily granularity
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            $salesByDate = $sales->groupBy(fn (Sale $s) => Carbon::parse($s->order_date)->toDateString());
            $paymentsByDate = $payments->groupBy(fn (SalePayment $p) => Carbon::parse($p->payment_date)->toDateString());

            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $labels[] = $date->format('d M');
                $revenueData[] = (float) ($salesByDate->get($dateKey)?->sum('total_amount') ?? 0);
                $paymentData[] = (float) ($paymentsByDate->get($dateKey)?->sum('amount') ?? 0);
            }
        } else {
            // Monthly granularity
            $period = CarbonPeriod::create($startDate->startOfMonth(), '1 month', $endDate->endOfMonth());
            $salesByMonth = $sales->groupBy(fn (Sale $s) => Carbon::parse($s->order_date)->format('Y-m'));
            $paymentsByMonth = $payments->groupBy(fn (SalePayment $p) => Carbon::parse($p->payment_date)->format('Y-m'));

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M Y');
                $revenueData[] = (float) ($salesByMonth->get($monthKey)?->sum('total_amount') ?? 0);
                $paymentData[] = (float) ($paymentsByMonth->get($monthKey)?->sum('amount') ?? 0);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Order Revenue Invoiced (₦)',
                    'data' => $revenueData,
                    'borderColor' => '#9f3e07',
                    'backgroundColor' => 'rgba(159, 62, 7, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Cash Collected (₦)',
                    'data' => $paymentData,
                    'borderColor' => '#2c694e',
                    'backgroundColor' => 'rgba(44, 105, 78, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Compute order counts grouped by status.
     */
    public function getOrderStatusChartData(?array $filters): array
    {
        $sales = $this->getSalesQuery($filters)->get();

        $statuses = [
            SaleStatus::PendingPayment->value => ['label' => 'Pending Payment', 'color' => '#7f5420'],
            SaleStatus::Processing->value => ['label' => 'Processing / Sourcing', 'color' => '#0284c7'],
            SaleStatus::Inspection->value => ['label' => 'Quality Inspection', 'color' => '#9f3e07'],
            SaleStatus::Dispatched->value => ['label' => 'Dispatched', 'color' => '#4b5563'],
            SaleStatus::Delivered->value => ['label' => 'Delivered', 'color' => '#16a34a'],
            SaleStatus::Completed->value => ['label' => 'Completed', 'color' => '#2c694e'],
            SaleStatus::Cancelled->value => ['label' => 'Cancelled', 'color' => '#ba1a1a'],
        ];

        $countsByStatus = $sales->groupBy(function (Sale $s) {
            return $s->status instanceof SaleStatus ? $s->status->value : (string) $s->status;
        });

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $key => $meta) {
            $count = $countsByStatus->get($key)?->count() ?? 0;
            if ($count > 0 || $sales->isEmpty()) {
                $labels[] = $meta['label'];
                $data[] = $count;
                $colors[] = $meta['color'];
            }
        }

        if (empty($data)) {
            $labels = ['No orders in period'];
            $data = [0];
            $colors = ['#9ca3af'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Compute geographic breakdown of orders & revenue by destination country.
     */
    public function getDestinationCountryChartData(?array $filters): array
    {
        $sales = $this->getSalesQuery($filters)->get();

        $byCountry = $sales->groupBy('destination_country')
            ->map(fn (Collection $group) => (float) $group->sum('total_amount'))
            ->sortDesc();

        if ($byCountry->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Sales (₦)',
                        'data' => [0],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['No sales recorded'],
            ];
        }

        $palette = [
            '#9f3e07', '#2c694e', '#7f5420', '#1e40af', '#6b21a8',
            '#0f766e', '#c2410c', '#374151', '#b91c1c', '#047857',
        ];

        $labels = $byCountry->keys()->all();
        $data = $byCountry->values()->all();
        $colors = array_slice($palette, 0, count($labels));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₦)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Compute payment methods share for cash inflow.
     */
    public function getPaymentMethodChartData(?array $filters): array
    {
        $payments = $this->getPaymentsQuery($filters)->get();

        $methodLabels = [
            'paystack' => 'Paystack Online',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'pos' => 'POS / Card',
            'mobile_money' => 'Mobile Money',
            'escrow' => 'Escrow',
            'other' => 'Other',
        ];

        $byMethod = $payments->groupBy('payment_method')
            ->map(fn (Collection $group) => (float) $group->sum('amount'))
            ->sortDesc();

        if ($byMethod->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Amount (₦)',
                        'data' => [0],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['No payments in period'],
            ];
        }

        $palette = ['#2c694e', '#9f3e07', '#1e40af', '#7f5420', '#6b21a8', '#374151'];

        $labels = [];
        $data = [];
        $colors = [];
        $i = 0;

        foreach ($byMethod as $method => $amount) {
            $labels[] = $methodLabels[$method] ?? ucfirst($method);
            $data[] = $amount;
            $colors[] = $palette[$i % count($palette)];
            $i++;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Collections (₦)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Compute top products sold by units and revenue within period.
     */
    public function getTopProducts(?array $filters, int $limit = 6): Collection
    {
        $saleIds = $this->getSalesQuery($filters)->pluck('id');

        return SaleItem::query()
            ->whereIn('sale_id', $saleIds)
            ->selectRaw('item_name, item_sku, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue, COUNT(DISTINCT sale_id) as order_occurrences')
            ->groupBy('item_name', 'item_sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Compute sales performance per staff member.
     */
    public function getStaffPerformance(?array $filters): Collection
    {
        $sales = $this->getSalesQuery($filters)->with(['creator'])->get();

        return $sales->groupBy('created_by')
            ->map(function (Collection $staffSales, $userId) {
                /** @var User|null $user */
                $user = $staffSales->first()?->creator;
                $grossSales = (float) $staffSales->sum('total_amount');
                $amountPaid = (float) $staffSales->sum('amount_paid');
                $balanceDue = (float) $staffSales->sum('balance_due');
                $ordersCount = $staffSales->count();
                $completedCount = $staffSales->filter(fn (Sale $s) => $s->isCompleted())->count();

                return [
                    'user_id' => $userId,
                    'name' => $user?->name ?? 'System / Direct',
                    'email' => $user?->email ?? '-',
                    'orders_count' => $ordersCount,
                    'completed_count' => $completedCount,
                    'gross_sales' => $grossSales,
                    'amount_paid' => $amountPaid,
                    'balance_due' => $balanceDue,
                    'collection_rate' => $grossSales > 0 ? round(($amountPaid / $grossSales) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('gross_sales')
            ->values();
    }

    /**
     * Split period into 7 intervals to create a sparkline array.
     */
    protected function calculateSparklinePoints(Collection $sales, Carbon $start, Carbon $end): array
    {
        $days = max(1, $start->diffInDays($end));
        $step = max(1, (int) ceil($days / 7));

        $points = [];
        $current = $start->copy();

        for ($i = 0; $i < 7; $i++) {
            $intervalEnd = $current->copy()->addDays($step)->endOfDay();
            if ($intervalEnd->gt($end)) {
                $intervalEnd = $end->copy();
            }

            $amount = (float) $sales
                ->filter(function (Sale $s) use ($current, $intervalEnd) {
                    $orderDate = Carbon::parse($s->order_date);
                    return $orderDate->betweenIncluded($current, $intervalEnd);
                })
                ->sum('total_amount');

            $points[] = $amount;
            $current = $intervalEnd->copy()->addDay()->startOfDay();

            if ($current->gt($end)) {
                break;
            }
        }

        while (count($points) < 7) {
            $points[] = 0.0;
        }

        return $points;
    }
}
