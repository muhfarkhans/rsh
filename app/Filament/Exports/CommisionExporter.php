<?php

namespace App\Filament\Exports;

use App\Models\Commision;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CommisionExporter extends Exporter
{
    protected static ?string $model = User::class;

    public function getFileName(Export $export): string
    {
        return "Commision-{$export->getKey()}.csv";
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('commision')
                ->label('Commision'),
            ExportColumn::make('total_service')
                ->label('Total Service'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payroll export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
