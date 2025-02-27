<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

class PayrollReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.payroll-report';

    protected static ?string $navigationGroup = 'Reports';
}
