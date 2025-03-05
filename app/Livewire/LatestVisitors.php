<?php

namespace App\Livewire;

use App\Constants\VisitStatus;
use App\Models\ClientVisit;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVisitors extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ClientVisit::query())
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('client.reg_id')
                    ->label('Registrasi Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('therapy.name')
                    ->label('Terapis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            VisitStatus::REGISTER => 'warning',
                            VisitStatus::WAITING_FOR_CHECK => 'warning',
                            VisitStatus::WAITING_FOR_SERVICE => 'warning',
                            VisitStatus::ON_SERVICE => 'success',
                            VisitStatus::WAITING_FOR_PAYMENT => 'success',
                            VisitStatus::DONE => 'info',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            VisitStatus::REGISTER => 'Pendaftaran',
                            VisitStatus::WAITING_FOR_CHECK => 'Menunggu pengkajian',
                            VisitStatus::WAITING_FOR_SERVICE => 'Menunggu layanan',
                            VisitStatus::ON_SERVICE => 'Dilakukan pelayanan',
                            VisitStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                            VisitStatus::DONE => 'Selesai',
                            default => $record->status,
                        };
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal kunjungan')
                    ->formatStateUsing(function (string $state): string {
                        $diff = Carbon::parse($state)->diffForHumans();
                        return __("{$state} ({$diff})");
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
