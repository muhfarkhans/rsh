<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\VisitResource\Pages;
use App\Filament\App\Resources\VisitResource\RelationManagers;
use App\Helpers\FilamentHelper;
use App\Models\Client;
use App\Models\ClientVisit;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitResource extends Resource
{
    protected static ?string $model = ClientVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->columns(3)->schema(
                    [
                        Section::make('Client data')
                            ->headerActions([
                                Action::make('edit')
                                    ->label('Edit')
                                    ->icon('heroicon-o-pencil')
                                    ->fillForm(function (ClientVisit $record) {
                                        return [
                                            'name' => $record->client->name,
                                            'phone' => $record->client->phone,
                                            'birthdate' => $record->client->birthdate,
                                            'gender' => $record->client->gender,
                                            'job' => $record->client->job,
                                            'address' => $record->client->address,
                                        ];
                                    })
                                    ->form(function (Form $form) {
                                        return $form->schema([
                                            TextInput::make('name')
                                                ->label('Nama')
                                                ->required(),
                                            TextInput::make('phone')
                                                ->numeric()
                                                ->required()
                                                ->default('62')
                                                ->regex('/^62[0-9]{9,15}$/')
                                                ->label('No Telepon'),
                                            DatePicker::make('birthdate')
                                                ->label('Tanggal Lahir')
                                                ->required(),
                                            Select::make('gender')
                                                ->label('Jenis kelamin')
                                                ->required()
                                                ->options([
                                                    'Laki-laki' => 'Laki-laki',
                                                    'Perempuan' => 'Perempuan',
                                                ]),
                                            TextInput::make('job')
                                                ->label('Pekerjaan')
                                                ->required()
                                                ->columnSpan(2),
                                            Textarea::make('address')
                                                ->label('Alamat')
                                                ->required()
                                                ->columnSpan(2),
                                        ])->columns(2);
                                    })
                                    ->action(function (ClientVisit $record, array $data) {
                                        DB::transaction(function () use ($record, $data) {
                                            if ($data) {
                                                Client::where('id', $record->client_id)->update([
                                                    'name' => $data['name'],
                                                    'phone' => $data['phone'],
                                                    'birthdate' => $data['birthdate'],
                                                    'gender' => $data['gender'],
                                                    'job' => $data['job'],
                                                    'address' => $data['address'],
                                                ]);
                                            }
                                        });

                                        Notification::make()
                                            ->title('Client updated successfully')
                                            ->success()
                                            ->send();
                                    }),
                            ])
                            ->schema([
                                TextEntry::make('client.name')
                                    ->label('Nama Lengkap')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('client.phone')
                                    ->label('No. Telepon / HP')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->getStateUsing(fn($record) => filled($record->client->phone) ? $record->client->phone : 'N/A'),
                                TextEntry::make('client.birthdate')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->label('Tanggal lahir'),
                                TextEntry::make('client.gender')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->label('Jenis kelamin'),
                                TextEntry::make('client.job')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->label('Pekerjaan'),
                                TextEntry::make('client.address')
                                    ->label('Alamat')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('client.created_at')->label('Created at'),
                                TextEntry::make('client.updated_at')->label('Last updated at'),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Titik bekam')
                            ->description('Berikan tanda dimana akan dilakukan bekam')
                            ->schema([
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('generateResetLink')
                                        ->action(function (ClientVisit $record) {
                                            Notification::make()
                                                ->title('Not yet')
                                                ->body("still working on it")
                                                ->success()
                                                ->send();
                                        })
                                        ->label('Tentukan titik bekam')
                                        ->color('success')
                                        ->icon('heroicon-m-map-pin')
                                        ->iconPosition(IconPosition::After)
                                ])->fullWidth(),
                            ])
                            ->columnSpan(1),
                    ]
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.reg_id')
                    ->label('Registrasi Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Tanggal kunjungan')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
            'view' => Pages\ViewVisit::route('/{record}'),
        ];
    }
}
