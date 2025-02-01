<?php

namespace App\Filament\Resources\MasterData\ServiceResource\Pages;

use App\Filament\Resources\MasterData\ServiceResource;
use Filament\Actions;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Get;
use Filament\Forms\Set;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nama')
                ->placeholder('Isi nama layanan')
                ->required()
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Harga')
                ->hint('Isi harga layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columns(1),
            TextInput::make('duration')
                ->label('Durasi')
                ->hint('Isi durasi layanan dalam menit')
                ->required()
                ->numeric()
                ->suffix('Menit')
                ->columns(1),
            TextInput::make('commision')
                ->label('Persentase Komisi')
                ->hint('Isi persen jumlah komisi')
                ->required()
                ->numeric()
                ->maxValue(100)
                ->suffix("%")
                ->live(true)
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    $price = (int) $get('price');
                    $discount = ((int) $state / 100) * $price;
                    $set('commision_amount', $discount);
                })
                ->columns(1),
            TextInput::make('commision_amount')
                ->readOnly()
                ->label('Komisi')
                ->hint('Perhitungan jumlah komisi layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columns(1),
            Toggle::make('is_cupping')
                ->label('Apakah layanan bekam?')
                ->default(false)
        ];
    }
}
