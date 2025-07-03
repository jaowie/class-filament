<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Livewire\Notifications;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\View\Components\Modal;
use App\Filament\Resources\LivestockResource\Widgets\DashboardLivestockStats;
use App\Filament\Resources\LivestockResource\Widgets\LivestockDistribution;
use App\Filament\Resources\LivestockResource\Widgets\TotalCollectionYearly;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Modal::closedByClickingAway(false);
        Notifications::alignment(Alignment::Center);
        return $panel
            ->brandName('CLASS')
            ->brandLogo(fn () => view('filament.components.DC-logo'))
            ->brandLogoHeight('3rem')
            ->default()
            ->profile()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => '#000080',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                DashboardLivestockStats::class,
                LivestockDistribution::class,
                TotalCollectionYearly::class
            ])
            ->middleware([
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
