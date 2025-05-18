<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
        FilamentView::registerRenderHook(
            'panels::head.start',
            fn(): string => '<meta name="csrf-token" content="' . csrf_token() . '" />',
        );

        FilamentAsset::register([
            Css::make('stylesheet', asset('leaflet.css')),
            Js::make('script', asset('leaflet.js')),
            Js::make('script', asset('html2canvas.min.js')),
            JS::make('script', asset('recta/recta.js')),
            JS::make('script', asset('print.js')),
        ]);

        FilamentView::registerRenderHook(
            name: PanelsRenderHook::HEAD_START,
            hook: fn() => new HtmlString('<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): string => Blade::render('<livewire:panel-shortcuts /> <livewire:info-navbar />'),
        );

        if (str(config('app.url'))->startsWith('https://') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
