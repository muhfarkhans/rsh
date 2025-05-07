<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            //
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::user()->id;

        return $data;
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('additional_cupping_price')
                ->label('Harga titik bekam tambahan')
                ->suffix('Rupiah')
                ->numeric()
                ->required()
                ->columnSpanFull(),
            TextInput::make('limit_cupping_point')
                ->visible(false)
                ->label('Batasan titik bekam')
                ->suffix('Titik')
                ->numeric()
                ->required()
                ->columnSpanFull(),
        ];
    }
}
