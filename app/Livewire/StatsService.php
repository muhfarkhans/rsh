<?php

namespace App\Livewire;

use Carbon\Carbon;
use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class StatsService extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $clientServices = DB::table('services')
            ->select('services.id', 'services.name')
            ->selectRaw('count(services.id) as total')
            ->rightJoin('client_visit_cuppings', 'client_visit_cuppings.service_id', '=', 'services.id')
            ->whereBetween(
                'client_visit_cuppings.created_at',
                [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total')
            ->get();

        $serviceListHtml = '';
        foreach ($clientServices as $service) {
            $serviceListHtml .= '<li>' . $service->name . ' - ' . $service->total . 'x</li>';
        }

        return [
            Stat::make(
                'Layanan paling banyak di lakukan',
                $clientServices->first()->name . ' - ' . $clientServices->first()->total . 'x',
            )->description(
                    new HtmlString('
                    <ul>' . $serviceListHtml . '</ul>
                ')
                ),
        ];
    }

}
