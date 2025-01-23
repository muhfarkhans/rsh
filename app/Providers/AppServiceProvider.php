<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('stylesheet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            Js::make('script', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
        ]);

        FilamentView::registerRenderHook(
            name: PanelsRenderHook::HEAD_START,
            hook: fn() => new HtmlString('<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">'),
        );

        if (str(config('app.url'))->startsWith('https://') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
