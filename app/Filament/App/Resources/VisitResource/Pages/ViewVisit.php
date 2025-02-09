<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role as ConstRole;
use App\Constants\VisitStatus;
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
                                    ->url(function (ClientVisit $record) {
                                        return VisitResource::getUrl('edit', ['record' => $record]);
                                    })
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
                                    ->label('Tahun lahir')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->getStateUsing(fn($record) => date('Y', strtotime($record->client->birthdate))),
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
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(function ($record) {
                                        return match ($record->status) {
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
                                            VisitStatus::WAITING_FOR_CHECK => 'Menunggu check up',
                                            VisitStatus::WAITING_FOR_SERVICE => 'Menunggu layanan',
                                            VisitStatus::ON_SERVICE => 'Dilakukan pelayanan',
                                            VisitStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                                            VisitStatus::DONE => 'Selesai',
                                            default => $record->status,
                                        };
                                    }),
                                TextEntry::make('duration')
                                    ->label('Duration')
                                    ->getStateUsing(function ($record) {
                                        $start = Carbon::parse($record->started_at);
                                        $end = Carbon::parse($record->ended_at);

                                        $durationInMinutes = $start->diffInMinutes($end);
                                        return round($durationInMinutes) . " Menit";
                                    })
                                    ->hidden(function ($record) {
                                        if ($record->started_at == null && $record->ended_at == null) {
                                            return true;
                                        }

                                        return false;
                                    }),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Pilih layanan')
                            ->description('Tentukan layanan yang diinginkan')
                            ->schema([
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('cuppingpoint')
                                        ->url(function (ClientVisit $record) {
                                            return VisitResource::getUrl('edit-service', ['record' => $record]);
                                        })
                                        ->label(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping != null) {
                                                return 'Lihat Layanan';
                                            } else {
                                                return 'Check Up dan Pilih Layanan';
                                            }
                                        })
                                        ->color('success')
                                        ->icon('heroicon-m-map-pin')
                                        ->iconPosition(IconPosition::After)
                                    // ->hidden(function (ClientVisit $record) {
                                    //     // if ($record->clientVisitCupping != null) {
                                    //     //     return 'Update Layanan';
                                    //     // } else {
                                    //     //     return false;
                                    //     // }

                                    //     return false;
                                    //     // return !($record->status == VisitStatus::WAITING_FOR_SERVICE);
                                    // })
                                ])->fullWidth(),
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('viewcuppingpoint')
                                        ->label('Mulai Layanan')
                                        ->color('info')
                                        ->iconPosition(IconPosition::After)
                                        ->hidden(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping === null) {
                                                return true;
                                            }

                                            return !($record->status == VisitStatus::WAITING_FOR_SERVICE);
                                        })
                                        ->action(function (ClientVisit $record, array $data) {
                                            DB::transaction(function () use ($record, $data) {
                                                ClientVisit::where('id', $record->id)
                                                    ->update([
                                                        'status' => VisitStatus::ON_SERVICE,
                                                        'started_at' => now(),
                                                    ]);
                                            });

                                            Notification::make()
                                                ->title('Layanan dimulai')
                                                ->success()
                                                ->send();

                                            $this->record->refresh();
                                        }),
                                ])->fullWidth(),
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('viewcuppingpoint')
                                        ->label('Selesaikan Layanan')
                                        ->color('danger')
                                        ->iconPosition(IconPosition::After)
                                        ->hidden(function (ClientVisit $record) {
                                            if ($record->clientVisitCupping === null) {
                                                return true;
                                            }

                                            return !($record->status == VisitStatus::ON_SERVICE);
                                        })
                                        ->action(function (ClientVisit $record, array $data) {
                                            DB::transaction(function () use ($record, $data) {
                                                ClientVisit::where('id', $record->id)
                                                    ->update([
                                                        'status' => VisitStatus::WAITING_FOR_PAYMENT,
                                                        'ended_at' => now(),
                                                    ]);
                                            });

                                            Notification::make()
                                                ->title('Layanan selesai')
                                                ->success()
                                                ->send();

                                            $this->record->refresh();
                                        }),
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
                                            if ($record->clientVisitCupping === null) {
                                                return true;
                                            }

                                            return $record->clientVisitCupping->service->is_cupping === 0;
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
