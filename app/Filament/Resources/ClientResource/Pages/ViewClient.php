<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\ClientVisit;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.resources.client-resource.pages.view-client';

    function mutateFormDataBeforeFill(array $data): array
    {
        dd($this->record);
        $clientVisit = ClientVisit::where('client_id', $this->record->id)->orderBy('created_at', 'desc')->get();

        return $data;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->columns(3)->schema([
                    Section::make('Data Client')
                        ->description('Informasi profile client')
                        ->schema([
                            Grid::make()->columns(2)->schema([
                                TextEntry::make('reg_id')
                                    ->label('No Registrasi')
                                    ->columnSpanFull(),
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->columnSpanFull(),
                                TextEntry::make('phone')
                                    ->label('No Telepon'),
                                TextEntry::make('job')
                                    ->label('Pekerjaan'),
                                TextEntry::make('job')
                                    ->label('Pekerjaan'),
                                TextEntry::make('gender')
                                    ->label('Jenis Kelamin'),
                                TextEntry::make('address')
                                    ->label('Alamat')
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                        ])->columnSpan(2),
                    Section::make('Informasi Tambahan')->schema([
                        TextEntry::make('total_visit')
                            ->label('Jumlah Kunjungan')
                            ->state(function ($record) {
                                return new HtmlString(
                                    count($record->visits) . ' X'
                                );
                            }),
                        TextEntry::make('last_visit')
                            ->label('Kunjungan Terakhir')
                            ->state(function ($record) {
                                $last = $record->visits[count($record->visits) - 1];

                                return $last->created_at;
                            }),
                    ])->columnSpan(1),
                ])
            ]);
    }
}
