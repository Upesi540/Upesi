<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\MyWallet;
use App\Filament\Widgets\UserWalletOverview;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->sidebarCollapsibleOnDesktop() // Ajoute cette ligne
            ->userMenuItems([
                Action::make('wallet')
                    ->label(fn() => Auth::user()->wallet?->formatted_available_balance ?? '0 FCFA')
                    ->icon('heroicon-o-wallet')
                    ->color('success')
                // ->url(fn() => MyWallet::getUrl()), // Redirige vers son portefeuille
            ])
            ->navigationItems([
                NavigationItem::make('Site Upesi')
                    ->url(config('app.frontend_url', 'http://localhost:9200'))
                    ->icon('heroicon-o-globe-alt')
                    // ->group('Navigation')
                    ->sort(1)
                    ->openUrlInNewTab(),
            ])
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile(isSimple: false)
            // ->brandName('UPESI Admin') // 👈 Change le texte en haut à gauche
            ->colors([
                // 'primary' => Color::Amber,
                'primary' => [
                    50 => '#e6f0e8',
                    100 => '#cce0d1',
                    200 => '#99c2a3',
                    300 => '#66a375',
                    400 => '#338547',
                    500 => '#00712D',
                    600 => '#1b4d23',
                    700 => '#163e1c',
                    800 => '#102e15',
                    900 => '#0b1f0e',
                    950 => '#0b1f0e',
                ],
                'warning' => ' #FF9100',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                UserWalletOverview::class
                // FilamentInfoWidget::class,
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
            ])->spa(hasPrefetching: true)
            ->brandLogoHeight('2rem')
            ->brandLogo(asset('logo.png')) // 👈 Ajoute un logo personnalisé (assure-toi que le chemin est correct)
            ->favicon(asset('favicon.png'))
            ->unsavedChangesAlerts()
            ->databaseNotifications();
    }
}
