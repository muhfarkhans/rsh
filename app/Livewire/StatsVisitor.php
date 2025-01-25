<?php

namespace App\Livewire;

use App\Constants\VisitStatus;
use App\Models\ClientVisit;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsVisitor extends BaseWidget
{
    public ClientVisit $clientVisit;

    public function __construct()
    {
        //
    }

    protected function getStats(): array
    {
        return [
            Stat::make(
                'Jumlah Kunjungan Menunggu Layanan',
                ClientVisit::where('status', VisitStatus::WAITING_FOR_SERVICE)
                    ->whereDate('created_at', Carbon::today())
                    ->count()
            ),
            Stat::make(
                'Jumlah Kunjungan Hari Ini',
                ClientVisit::whereDate('created_at', Carbon::today())
                    ->count()
            ),
            Stat::make(
                'Jumlah Kunjungan Bulan Ini',
                ClientVisit::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count()
            ),
        ];
    }
}
