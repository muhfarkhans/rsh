<?php

namespace App\Livewire;

use App\Models\Service;
use DB;
use Filament\Widgets\ChartWidget;

class ServiceByGenderChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    protected static ?string $heading = 'Trend Layanan berdasarkan jenis kelamin client bulan ini';

    protected function getData(): array
    {

        $totalMale = DB::table('client_visits')
            ->join('clients', 'clients.id', '=', 'client_visits.id')
            ->where('clients.gender', 'Laki-laki')
            ->count();

        $totalFemale = DB::table('client_visits')
            ->join('clients', 'clients.id', '=', 'client_visits.id')
            ->where('clients.gender', 'Perempuan')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jenis Kelamin',
                    'data' => [$totalFemale, $totalMale]
                ],
            ],
            'labels' => ['Perempuan', 'Laki-laki'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
