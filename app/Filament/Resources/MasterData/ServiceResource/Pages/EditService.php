<?php

namespace App\Filament\Resources\MasterData\ServiceResource\Pages;

use App\Filament\Resources\MasterData\ServiceResource;
use Filament\Actions;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nama')
                ->placeholder('Isi nama layanan')
                ->required()
                ->columnSpan(1),
            TextInput::make('price')
                ->label('Harga')
                ->hint('Isi harga layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columnSpan(1),
            TextInput::make('duration')
                ->label('Durasi')
                ->hint('Isi durasi layanan dalam menit')
                ->required()
                ->numeric()
                ->suffix('Menit')
                ->columnSpan(1),
            TextInput::make('commision')
                ->label('Komisi')
                ->hint('Isi harga komisi')
                ->required()
                ->lt('price')
                ->numeric()
                ->prefix('Rp. ')
                ->columnSpan(1),
            Toggle::make('is_cupping')
                ->label('Apakah layanan bekam?')
                ->default(false)
                ->columnSpan(2)
        ];
    }
}
