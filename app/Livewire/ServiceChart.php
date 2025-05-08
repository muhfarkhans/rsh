<?php

namespace App\Livewire;

use App\Models\Service;
use Carbon\Carbon;
use DB;
use Filament\Widgets\ChartWidget;

class ServiceChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Layanan bulan ini';

    protected function getData(): array
    {
        $service = Service::get();
        $serviceId = $service->pluck('id');
        $serviceName = $service->pluck('name');

        $clientServices = DB::table('services')
            ->select('services.id')
            ->selectRaw('count(services.id) as total')
            ->rightJoin('client_visit_cuppings', 'client_visit_cuppings.service_id', '=', 'services.id')
            ->whereBetween(
                'client_visit_cuppings.created_at',
                [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]
            )
            ->groupBy('services.id')
            ->get();

        $data = [];
        foreach ($serviceId as $key => $item) {
            $data[$key] = 0;
            foreach ($clientServices as $clientService) {
                if ($item == $clientService->id) {
                    $data[$key] = $clientService->total;
                    continue;
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Layanan',
                    'data' => $data
                ],
            ],
            'labels' => $serviceName,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
