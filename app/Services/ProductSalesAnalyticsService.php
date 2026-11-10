<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Item-level sales analysis: what sells, by category / product / supplier, over any period.
 * Cancelled and soft-deleted sales are excluded.
 */
class ProductSalesAnalyticsService
{
    public function __construct(protected SalesAnalyticsService $dates) {}

    /**
     * @param  array<string, mixed>|null  $filters  period, start_date, end_date, category_id, supplier_id
     */
    public function itemsQuery(?array $filters): Builder
    {
        $range = $this->dates->resolveDateRange($filters);

        $query = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->whereNull('sales.deleted_at')
            ->where('sales.status', '!=', SaleStatus::Cancelled->value)
            ->whereDate('sales.order_date', '>=', $range['start']->toDateString())
            ->whereDate('sales.order_date', '<=', $range['end']->toDateString());

        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('products.supplier_id', $filters['supplier_id']);
        }

        return $query;
    }

    /** Aggregate select for grouped tables (id column keeps Filament happy). */
    public function byCategoryQuery(?array $filters): Builder
    {
        return $this->itemsQuery($filters)
            ->selectRaw("COALESCE(categories.id, 0) as id, COALESCE(categories.name, 'Uncategorised') as label, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.total_price) as total_revenue, COUNT(DISTINCT sale_items.sale_id) as orders_count, COUNT(DISTINCT COALESCE(sale_items.product_id, sale_items.item_name)) as products_count")
            ->groupBy('categories.id', 'categories.name');
    }

    public function bySupplierQuery(?array $filters): Builder
    {
        return $this->itemsQuery($filters)
            ->selectRaw("COALESCE(suppliers.id, 0) as id, COALESCE(suppliers.name, 'No supplier') as label, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.total_price) as total_revenue, COUNT(DISTINCT sale_items.sale_id) as orders_count, COUNT(DISTINCT COALESCE(sale_items.product_id, sale_items.item_name)) as products_count")
            ->groupBy('suppliers.id', 'suppliers.name');
    }

    public function byProductQuery(?array $filters): Builder
    {
        return $this->itemsQuery($filters)
            ->selectRaw("MIN(sale_items.id) as id, sale_items.item_name, sale_items.item_sku, MAX(categories.name) as category_name, MAX(suppliers.name) as supplier_name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.total_price) as total_revenue, COUNT(DISTINCT sale_items.sale_id) as orders_count")
            ->groupBy('sale_items.item_name', 'sale_items.item_sku');
    }

    /** @return array{units: int, revenue: float, orders: int, products: int, label: string} */
    public function summary(?array $filters): array
    {
        $row = $this->itemsQuery($filters)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as units, COALESCE(SUM(sale_items.total_price), 0) as revenue, COUNT(DISTINCT sale_items.sale_id) as orders, COUNT(DISTINCT sale_items.item_name) as products')
            ->first();

        return [
            'units' => (int) $row->units,
            'revenue' => (float) $row->revenue,
            'orders' => (int) $row->orders,
            'products' => (int) $row->products,
            'label' => $this->dates->resolveDateRange($filters)['label'],
        ];
    }

    /**
     * Units and revenue per day (ranges up to 62 days) or per month (longer ranges).
     *
     * @return Collection<string, object>
     */
    public function trend(?array $filters): Collection
    {
        $range = $this->dates->resolveDateRange($filters);
        $daily = $range['start']->diffInDays($range['end']) <= 62;
        $driver = DB::connection()->getDriverName();

        $format = $daily ? '%Y-%m-%d' : '%Y-%m';
        $expr = match (true) {
            $driver === 'sqlite' => "strftime('{$format}', sales.order_date)",
            default => "DATE_FORMAT(sales.order_date, '{$format}')",
        };

        return $this->itemsQuery($filters)
            ->selectRaw("{$expr} as bucket, SUM(sale_items.quantity) as units, SUM(sale_items.total_price) as revenue")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');
    }
}

