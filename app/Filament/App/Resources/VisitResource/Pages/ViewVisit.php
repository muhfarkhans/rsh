<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role as ConstRole;
use App\Filament\App\Resources\VisitResource;
use App\Helpers\FilamentHelper;
use App\Models\ClientVisit;
use App\Models\Client;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitResource::class;

    public function infolist(Infolist $infolist): Infolist
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
                                            'therapy_id' => $record->therapy_id,
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
                                                ->label('Tahun Lahir')
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
                                            Select::make('therapy_id')
                                                ->label('Nama Terapis')
                                                ->options(function () {
                                                    return User::with(['roles'])->whereHas('roles', function ($query) {
                                                        return $query->where('name', ConstRole::THERAPIST);
                                                    })->get()->pluck('name', 'id');
                                                })
                                                ->live()
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->columnSpanFull(),
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

                                                ClientVisit::where('id', $record->id)
                                                    ->update(['therapy_id' => $data['therapy_id']]);
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
                                    Action::make('cuppingpoint')
                                        ->url(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping) {
                                                return route('filament.app.resources.cuppings.edit', ['record' => $record->clientVisitCupping]);
                                            } else {
                                                return route('filament.app.resources.cuppings.create', ['visit' => $record->id]);
                                            }
                                        })
                                        ->label(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping != null) {
                                                return 'Update titik bekam';
                                            } else {
                                                return 'Tentukan titik bekam';
                                            }
                                        })
                                        ->color('success')
                                        ->icon('heroicon-m-map-pin')
                                        ->iconPosition(IconPosition::After),
                                ])->fullWidth(),
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('viewcuppingpoint')
                                        ->url(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping) {
                                                return route('filament.app.resources.cuppings.cupping-point', ['record' => $record->clientVisitCupping]);
                                            } else {
                                                return '';
                                            }
                                        })
                                        ->label('Lihat titik bekam')
                                        ->color('info')
                                        ->icon('heroicon-m-map-pin')
                                        ->iconPosition(IconPosition::After)
                                        ->hidden(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping == null) {
                                                return true;
                                            } else {
                                                return false;
                                            }
                                        }),
                                ])->fullWidth(),
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('viewcuppingpoint')
                                        ->url(function (ClientVisit $record) {
                                            return url('') . '/pdf/12';
                                        })
                                        ->label('Generate PDF')
                                        ->color('info')
                                        ->icon('heroicon-m-document-arrow-down')
                                        ->iconPosition(IconPosition::After),
                                ])->fullWidth(),
                                // \Filament\Infolists\Components\Actions::make([
                                //     Action::make('viewcuppingpoint')
                                //         ->label('Generate PDF')
                                //         ->color('info')
                                //         ->icon('heroicon-m-document-arrow-down')
                                //         ->iconPosition(IconPosition::After)
                                //         ->action(function (Model $record) {
                                //             return response()->streamDownload(function () use ($record) {
                                //                 echo Pdf::loadHtml(
                                //                     Blade::render('pdf', ['view' => $record])
                                //                 )->stream();
                                //             }, $record->id . '.pdf');
                                //         })
                                // ])->fullWidth()
                            ])
                            ->columnSpan(1),
                        Section::make('Riwayat Penyakit')
                            ->schema([
                                TextEntry::make('complaint')
                                    ->label('Keluhan yang dirasakan')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                TextEntry::make('medical_history')
                                    ->label('Riwayat medis')
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('family_medical_history')
                                    ->label('Riwayat penyakit keluarga')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('medication_history')
                                    ->label('Riwayat pengobatan')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('sleep_habits_start')
                                        ->label('Waktu tidur')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->sleep_habits['start']),
                                    TextEntry::make('sleep_habits_end')
                                        ->label('Waktu bangun')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->sleep_habits['end']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(3)->schema([
                                    TextEntry::make('exercise_name')
                                        ->label('Jenis olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->exercise['name']),
                                    TextEntry::make('exercise_intensity')
                                        ->label('Intensitas olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->exercise['intensity']),
                                    TextEntry::make('exercise_time')
                                        ->label('Waktu olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->exercise['time']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('nutrition_name')
                                        ->label('Jenis makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->nutrition['name']),
                                    TextEntry::make('nutrition_portion')
                                        ->label('Porsi makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->nutrition['portion']),
                                    TextEntry::make('exercise_time')
                                        ->label('Waktu makan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->nutrition['time']),
                                    TextEntry::make('nutrition_type')
                                        ->label('Golongan makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->nutrition['type']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('spiritual_name')
                                        ->label('Ibadah wajib')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->spiritual['name']),
                                    TextEntry::make('spiritual_type')
                                        ->label('Jenis ibadah')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->getStateUsing(fn($record) => $record->spiritual['type']),
                                ])->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Pemeriksaan Fisik')
                            ->schema([
                                TextEntry::make('temperature')
                                    ->label('Suhu')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->check_other : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('blood_pressure')
                                    ->label('Tekanan darah')
                                    ->suffix('mm/Hg')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->blood_pressure : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('pulse')
                                    ->label('Nadi')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->pulse : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('respiratory')
                                    ->label('Frekuensi nafas')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->respiratory : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('weight')
                                    ->label('Berat Badan')
                                    ->suffix('kg')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->weight : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('height')
                                    ->label('Tinggi badan')
                                    ->suffix('cm')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->height : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                                TextEntry::make('check_other')
                                    ->label('Pemeriksaan lainnya')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->check_other : "-"))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Diagnosa')
                            ->schema([
                                TextEntry::make('diagnose')
                                    ->label('')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                    ]
                ),
            ]);
    }
}
