<?php

namespace App\Providers\Filament;

use App\Constants\Role;
use App\Filament\App\Pages\Dashboard;
use App\Filament\App\Pages\Login;
use App\Filament\Resources\TransactionResource;
use App\Livewire\LatestVisitors;
use App\Livewire\StatsVisitor;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->login(Login::class)
            ->widgets([
                StatsVisitor::class,
                // LatestVisitors::class,
            ])
            ->topNavigation()
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
                FilamentEditProfilePlugin::make()
                    ->setIcon('heroicon-o-user')
                    ->slug('my-profile')
                    ->setTitle(function () {
                        return Auth::user()->name;
                    })
                    ->setNavigationGroup('Users')
                    ->shouldShowBrowserSessionsForm()
                    ->shouldRegisterNavigation(false)
                    ->shouldShowAvatarForm()->canAccess(fn(): bool => Auth::check())->setNavigationLabel('My Profile'),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        return Auth::user()->name;
                    })
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
            ])
            ->navigationItems([
                NavigationItem::make('Transaction')
                    ->visible(function () {
                        if (in_array(Role::THERAPIST, Auth::user()->getRoleNames()->toArray())) {
                            return false;
                        }

                        return true;
                    })
                    ->url(fn() => TransactionResource::getUrl('index'))
                    ->icon('heroicon-o-banknotes')
                    ->sort(50),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
