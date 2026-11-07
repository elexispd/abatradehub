<?php

namespace App\Filament\Widgets;

use App\Enums\EnquiryStatus;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Enquiry;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $new = Enquiry::where('status', EnquiryStatus::New)->count();
        $thisWeek = Enquiry::where('created_at', '>=', now()->subWeek())->count();
        $published = Product::published()->count();
        $drafts = Product::where('status', 'draft')->count();
        $low = Product::lowStock()->count();

        return [
            Stat::make('New enquiries', $new)
                ->description("{$thisWeek} received in the last 7 days")
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('primary')
                ->url(EnquiryResource::getUrl('index')),
            Stat::make('Published products', $published)
                ->description("{$drafts} drafts")
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->url(ProductResource::getUrl('index')),
            Stat::make('Low stock', $low)
                ->description('At or below threshold')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($low > 0 ? 'danger' : 'success'),
        ];
    }
}