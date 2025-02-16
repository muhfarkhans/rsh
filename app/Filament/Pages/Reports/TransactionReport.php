<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\StatsTransaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\WidgetConfiguration;

class TransactionReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Reports';

    protected function getHeaderWidgets(): array
    {
        return [
            new WidgetConfiguration(StatsTransaction::class, ['isToday' => false])
        ];
    }

    protected static string $view = 'filament.pages.reports.transaction-report';
}


