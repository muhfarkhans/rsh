<?php

namespace App\Filament\Exports;

use App\Models\Setting;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;

class PayrollExporter extends Exporter
{
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('commision')
                ->label('Commision'),
            ExportColumn::make('total_presence')
                ->label('Total Presence')
                ->state(function ($record) {
                    $totalPresence = 0;
                    if ($record->total_service >= 1) {
                        $totalPresence = $record->total_presence;
                    } else {
                        $totalPresence = $record->total_presence / $record->total_service;
                    }

                    return $totalPresence;
                }),
            ExportColumn::make('total_presence_1')
                ->label('Attendance allowance')
                ->state(function ($record) {
                    $setting = Setting::where('id', 1)->first();

                    $totalPresence = 0;
                    if ($record->total_service >= 1) {
                        $totalPresence = $record->total_presence;
                    } else {
                        $totalPresence = $record->total_presence / $record->total_service;
                    }

                    return $totalPresence * $setting->attendance;
                }),
            ExportColumn::make('total_presence_2')
                ->label('Meal allowance')
                ->state(function ($record) {
                    $setting = Setting::where('id', 1)->first();

                    $totalPresence = 0;
                    if ($record->total_service >= 1) {
                        $totalPresence = $record->total_presence;
                    } else {
                        $totalPresence = $record->total_presence / $record->total_service;
                    }

                    return $totalPresence * $setting->attendance;
                    ;
                }),
            ExportColumn::make('total')
                ->label('Total')
                ->state(function ($record) {
                    $setting = Setting::where('id', 1)->first();

                    $totalPresence = 0;
                    if ($record->total_service >= 1) {
                        $totalPresence = $record->total_presence;
                    } else {
                        $totalPresence = $record->total_presence / $record->total_service;
                    }

                    return ($totalPresence * $setting->attendance) + ($totalPresence * $setting->meal) + $record->commision;
                }),
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
