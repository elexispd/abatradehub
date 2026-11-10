<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LatestEnquiries;
use App\Filament\Widgets\LowStockProducts;
use App\Filament\Widgets\StatsOverview;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(\App\Support\Admin::path())
            ->login()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->brandName('AbaTradeHub')
            ->brandLogo(fn () => asset('img/logo_light.png'))
            ->brandLogoHeight('5rem')
            ->favicon(fn () => asset('img/favicon.png'))
            ->font('Plus Jakarta Sans')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Sales',
                'Catalog',
                NavigationGroup::make('Analytics')->collapsible(),
                'Staff Management',
                'Filament Shield',
            ])
            ->colors([
                // Brand colours from designs/DESIGN.md (primary, secondary, tertiary, error).
                'primary' => Color::hex('#9f3e07'),
                'success' => Color::hex('#2c694e'),
                'warning' => Color::hex('#7f5420'),
                'danger' => Color::hex('#ba1a1a'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverview::class,
                LatestEnquiries::class,
                LowStockProducts::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->middleware([
                \App\Http\Middleware\PreventIndexing::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}


