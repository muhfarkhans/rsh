<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;

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
    }
}
