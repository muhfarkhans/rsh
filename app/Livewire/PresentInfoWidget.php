<?php

namespace App\Livewire;

use App\Models\Presence;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PresentInfoWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $clockIn = Presence::where('user_id', Auth::user()->id)->whereDate('created_at', now())->first();
        return [
            Stat::make(
                'Anda telah login pada pukul',
                $clockIn->created_at->format('d-m-Y H:s')
            ),
        ];
    }
}
