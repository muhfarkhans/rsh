<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('roles')
                ->relationship('roles', 'name')
                ->getOptionLabelFromRecordUsing(fn(Role $record) => str($record->name)->headline())
                ->preload()
                ->reactive()
                ->required()
                ->columns(1),
            TextInput::make('name')
                ->label('Nama Lengkap User')
                ->placeholder('isi nama lengkap')
                ->required()
                ->columns(1),
            TextInput::make('phone')
                ->label('Enter No. Telepon / HP')
                ->placeholder('62******')
                ->unique('users', 'phone', null, true)
                ->required()
                ->regex('/^62[0-9]{9,15}$/')
                ->columns(1),
            TextInput::make('email')
                ->label('Email')
                ->unique('users', 'email', null, true)
                ->placeholder('Email@domain.com')
                ->email()
                ->required()
                ->columns(1),
            TextInput::make('password')
                ->label('Password')
                ->placeholder('isi password')
                ->password()
                ->required()
                ->hidden(function (Get $get, string $operation) {
                    if ($operation === 'edit')
                        return true;
                })
                ->disabled(function (Get $get, string $operation) {
                    if ($operation === 'edit')
                        return true;
                })
                ->columns(1),
            TextInput::make('password_confirmation')
                ->label('Konfirmasi Password')
                ->placeholder('konfirmasi password')
                ->same('password')
                ->required()
                ->hidden(function (Get $get, string $operation) {
                    if ($operation === 'edit')
                        return true;
                })
                ->disabled(function (Get $get, string $operation) {
                    if ($operation === 'edit')
                        return true;
                })
                ->password()
                ->dehydrated(false)
                ->columns(1),
            Textarea::make('address')
                ->label('Alamat')
                ->placeholder("Alamat lengkap")
                ->required()
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('User Aktif?')
                ->default(true)
        ];
    }
}
