<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role as ConstRole;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\TransactionResource;
use App\Filament\App\Resources\VisitResource;
use App\Helpers\FilamentHelper;
use App\Helpers\Helper;
use App\Jobs\EmailNewVisitJob;
use App\Models\ClientVisit;
use App\Models\Client;
use App\Models\User;
use Auth;
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
                                    ->visible(function () {
                                        if (in_array($this->record->status, [VisitStatus::DONE, VisitStatus::WAITING_FOR_PAYMENT])) {
                                            return false;
                                        }

                                        if (in_array(ConstRole::THERAPIST, Auth::user()->getRoleNames()->toArray())) {
                                            return false;
                                        }

                                        return true;
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
                                            VisitStatus::WAITING_FOR_CHECK => 'Menunggu pengkajian',
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
                        Grid::make(1)->schema([
                            Section::make('Pilih layanan')
                                ->description('Tentukan layanan yang diinginkan')
                                ->schema([
                                    \Filament\Infolists\Components\Actions::make([
                                        Action::make('cuppingpoint')
                                            ->url(function (ClientVisit $record) {
                                                return VisitResource::getUrl('edit-service', ['record' => $record]);
                                            })
                                            ->label(function (ClientVisit $record) {
                                                if ($record->clientVisitCupping->temperature != null) {
                                                    return 'Edit dan Lihat Layanan';
                                                } else {
                                                    return 'Lakukan Pengkajian';
                                                }
                                            })
                                            ->visible(function () {
                                                if (in_array($this->record->status, [VisitStatus::DONE, VisitStatus::WAITING_FOR_PAYMENT])) {
                                                    return false;
                                                } else {
                                                    return true;
                                                }
                                            })
                                            ->color('success')
                                            ->icon('heroicon-m-map-pin')
                                            ->iconPosition(IconPosition::After)
                                    ])->fullWidth(),
                                    \Filament\Infolists\Components\Actions::make([
                                        Action::make('startservice')
                                            ->label('Mulai Layanan')
                                            ->color('info')
                                            ->iconPosition(IconPosition::After)
                                            ->visible(function (ClientVisit $record) {
                                                if ($record->clientVisitCupping === null) {
                                                    return false;
                                                }

                                                return ($record->status == VisitStatus::WAITING_FOR_SERVICE);
                                            })
                                            ->action(function (ClientVisit $record, array $data) {
                                                DB::transaction(function () use ($record, $data) {
                                                    ClientVisit::where('id', $record->id)
                                                        ->update([
                                                            'status' => VisitStatus::ON_SERVICE,
                                                            'started_at' => now(),
                                                        ]);
                                                });

                                                $emailPayload = [
                                                    'client_reg_id' => $record->client->reg_id,
                                                    'client_name' => $record->client->name,
                                                    'client_service' => $record->clientVisitCupping->service->name,
                                                    'client_service_price' => $record->clientVisitCupping->service->price,
                                                    'client_service_commision' => $record->clientVisitCupping->service->commision,
                                                    'client_service_is_cupping' => $record->clientVisitCupping->service->is_cupping,
                                                    'client_service_started_at' => now(),
                                                    'client_service_finished_at' => '-',
                                                    'client_service_status' => VisitStatus::ON_SERVICE,
                                                    'client_therapist' => $record->therapy->name,
                                                    'client_created_at' => $record->created_at,
                                                ];

                                                $idSuperAdmin = DB::table('roles')->where('name', ConstRole::SUPER_ADMIN)->first()->id;
                                                $users = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                                                    ->where('model_has_roles.role_id', $idSuperAdmin)
                                                    ->where('users.is_active', 1)
                                                    ->get();

                                                foreach ($users as $key => $admin) {
                                                    dispatch(new EmailNewVisitJob($emailPayload, $admin->email));
                                                }

                                                Notification::make()
                                                    ->title('Layanan dimulai')
                                                    ->success()
                                                    ->send();

                                                $this->record->refresh();
                                            }),
                                    ])->fullWidth(),
                                    \Filament\Infolists\Components\Actions::make([
                                        Action::make('finishservice')
                                            ->label('Selesaikan Layanan')
                                            ->color('danger')
                                            ->iconPosition(IconPosition::After)
                                            ->visible(function (ClientVisit $record) {
                                                if ($record->clientVisitCupping === null) {
                                                    return false;
                                                }

                                                return ($record->status == VisitStatus::ON_SERVICE);
                                            })
                                            ->action(function (ClientVisit $record, array $data) {
                                                DB::transaction(function () use ($record, $data) {
                                                    ClientVisit::where('id', $record->id)
                                                        ->update([
                                                            'status' => VisitStatus::WAITING_FOR_PAYMENT,
                                                            'ended_at' => now(),
                                                        ]);
                                                });

                                                $emailPayload = [
                                                    'client_reg_id' => $record->client->reg_id,
                                                    'client_name' => $record->client->name,
                                                    'client_service' => $record->clientVisitCupping->service->name,
                                                    'client_service_price' => $record->clientVisitCupping->service->price,
                                                    'client_service_commision' => $record->clientVisitCupping->service->commision,
                                                    'client_service_is_cupping' => $record->clientVisitCupping->service->is_cupping,
                                                    'client_service_started_at' => $record->started_at,
                                                    'client_service_finished_at' => now(),
                                                    'client_service_status' => VisitStatus::WAITING_FOR_PAYMENT,
                                                    'client_therapist' => $record->therapy->name,
                                                    'client_created_at' => $record->created_at,
                                                ];

                                                $idSuperAdmin = DB::table('roles')->where('name', ConstRole::SUPER_ADMIN)->first()->id;
                                                $users = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                                                    ->where('model_has_roles.role_id', $idSuperAdmin)
                                                    ->where('users.is_active', 1)
                                                    ->get();

                                                foreach ($users as $key => $admin) {
                                                    dispatch(new EmailNewVisitJob($emailPayload, $admin->email));
                                                }

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
                                            ->visible(function (ClientVisit $record) {
                                                if ($record->clientVisitCupping === null) {
                                                    return false;
                                                }

                                                return $record->clientVisitCupping->service->is_cupping !== 0;
                                            })
                                    ])->fullWidth(),
                                    \Filament\Infolists\Components\Actions::make([
                                        Action::make('generatepdf')
                                            ->action(function () {
                                                $data = [
                                                    'client_reg_id' => $this->record->client->reg_id,
                                                    'transaction_invoice_id' => $this->record->transactions->last()->invoice_id,
                                                    'client_name' => $this->record->client->reg_id,
                                                    'client_phone' => $this->record->client->phone,
                                                    'client_gender' => $this->record->client->gender,
                                                    'client_year' => $this->record->client->birthdate,
                                                    'job' => $this->record->client->job,
                                                    'address' => $this->record->client->address,
                                                    'visit_complaint' => $this->record->complaint,
                                                    'visit_medical_history' => $this->record->medical_history,
                                                    'visit_family_medical_history' => $this->record->family_medical_history,
                                                    'visit_medication_history' => $this->record->medication_history,
                                                    'visit_sleep_start' => $this->record->sleep_habits['start'],
                                                    'visit_sleep_end' => $this->record->sleep_habits['end'],
                                                    'visit_exercise_name' => $this->record->exercise['name'],
                                                    'visit_exercise_intensity' => $this->record->exercise['intensity'],
                                                    'visit_exercise_time' => $this->record->exercise['time'],
                                                    'visit_nutrition_name' => $this->record->nutrition['name'],
                                                    'visit_nutrition_portion' => $this->record->nutrition['portion'],
                                                    'visit_nutrition_time' => $this->record->nutrition['time'],
                                                    'visit_nutrition_type' => is_array($this->record->nutrition['type']) ? $this->record->nutrition['type'] : [],
                                                    'visit_spiritual_name' => $this->record->spiritual['name'],
                                                    'visit_spiritual_type' => is_array($this->record->nutrition['type']) ? $this->record->nutrition['type'] : [],
                                                    'visit_check_temperature' => $this->record->clientVisitCheck->temperature ?? 0,
                                                    'visit_check_blood_pressure' => $this->record->clientVisitCheck->blood_pressure ?? 0,
                                                    'visit_check_pulse' => $this->record->clientVisitCheck->pulse ?? 0,
                                                    'visit_check_respiratory' => $this->record->clientVisitCheck->respiratory ?? 0,
                                                    'visit_check_weight' => $this->record->clientVisitCheck->weight ?? 0 . " Kg",
                                                    'visit_check_height' => $this->record->clientVisitCheck->height ?? 0 . " cm",
                                                    'visit_check_other' => $this->record->clientVisitCheck->check_other ?? "",
                                                    'visit_diagnose' => $this->record->diagnose,
                                                    'service_name' => $this->record->clientVisitCupping->service->name,
                                                    'service_price' => $this->record->clientVisitCupping->service->price,
                                                    'service_therapist' => $this->record->clientVisitCupping->therapist->name,
                                                    'client_name_related' => $this->record->relation_as,
                                                    'signature_therapist' => Helper::getFileAsBase64($this->record->signature_therapist),
                                                    'signature_client' => Helper::getFileAsBase64($this->record->signature_client),
                                                ];

                                                $pdf = Pdf::loadView('pdf.detail', ['data' => $data]);
                                                return response()->streamDownload(function () use ($pdf) {
                                                    echo $pdf->stream();
                                                }, 'Detail-' . '.pdf');
                                            })
                                            ->visible(function () {
                                                if (count($this->record->transactions) > 0) {
                                                    return true;
                                                } else {
                                                    return false;
                                                }
                                            })
                                            ->label('Generate PDF')
                                            ->color('info')
                                            ->icon('heroicon-m-document-arrow-down')
                                            ->iconPosition(IconPosition::After),
                                    ])->fullWidth(),
                                ])
                                ->columnSpan(1),
                            Section::make('Detail Invoice')
                                ->description('Informasi tagihan transaksi')
                                ->schema([
                                    \Filament\Infolists\Components\Actions::make([
                                        Action::make('info_invoice')
                                            ->url(function (ClientVisit $record) {
                                                if ($record->transactions->last() != null) {
                                                    return TransactionResource::getUrl('view', ['record' => $record->transactions->last()]);
                                                }

                                                return url('');
                                            })
                                            ->label(function (ClientVisit $record) {
                                                $invoiceId = "";
                                                if ($record->transactions->last() != null) {
                                                    $invoiceId = $record->transactions->last()->invoice_id;
                                                }
                                                return "Invoice " . $invoiceId;
                                            })
                                            ->color('info')
                                    ])->fullWidth(),
                                ])
                                ->hidden(function (ClientVisit $record) {
                                    if (in_array(ConstRole::THERAPIST, Auth::user()->getRoleNames()->toArray())) {
                                        return true;
                                    }

                                    if ($record->transactions->last() == null) {
                                        return true;
                                    }

                                    if ($record->transaction == null) {
                                        return true;
                                    }

                                    return false;
                                })
                                ->columnSpan(1),
                        ])->columnSpan(1),
                        Section::make('Layanan')
                            ->schema([
                                TextEntry::make('therapy.name')
                                    ->label('Terapis')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 2,
                                        'lg' => 2
                                    ]),
                                TextEntry::make('clientVisitCupping.service.name')
                                    ->label('Layanan')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 2,
                                        'lg' => 2
                                    ]),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 4,
                                'lg' => 4,
                            ])
                            ->columnSpan(2),
                        Section::make('Riwayat Penyakit')
                            ->schema([
                                TextEntry::make('complaint')
                                    ->label('Keluhan yang dirasakan')
                                    ->weight(FontWeight::Bold)
                                    ->default("-")
                                    ->columnSpanFull(),
                                TextEntry::make('medical_history')
                                    ->label('Riwayat medis')
                                    ->listWithLineBreaks()
                                    ->default("-")
                                    ->bulleted(),
                                TextEntry::make('family_medical_history')
                                    ->label('Riwayat penyakit keluarga')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpanFull(),
                                TextEntry::make('medication_history')
                                    ->label('Riwayat pengobatan')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('sleep_habits_start')
                                        ->label('Waktu tidur')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->sleep_habits['start']),
                                    TextEntry::make('sleep_habits_end')
                                        ->label('Waktu bangun')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->sleep_habits['end']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(3)->schema([
                                    TextEntry::make('exercise_name')
                                        ->label('Jenis olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->exercise['name']),
                                    TextEntry::make('exercise_intensity')
                                        ->label('Intensitas olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->exercise['intensity']),
                                    TextEntry::make('exercise_time')
                                        ->label('Waktu olahraga')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->exercise['time']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('nutrition_name')
                                        ->label('Jenis makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->nutrition['name']),
                                    TextEntry::make('nutrition_portion')
                                        ->label('Porsi makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->nutrition['portion']),
                                    TextEntry::make('exercise_time')
                                        ->label('Waktu makan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->nutrition['time']),
                                    TextEntry::make('nutrition_type')
                                        ->label('Golongan makanan')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->nutrition['type']),
                                ])->columnSpanFull(),
                                Grid::make()->columns(2)->schema([
                                    TextEntry::make('spiritual_name')
                                        ->label('Ibadah wajib')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->spiritual['name']),
                                    TextEntry::make('spiritual_type')
                                        ->label('Jenis ibadah')
                                        ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                        ->default("-")
                                        ->getStateUsing(fn($record) => $record->spiritual['type']),
                                ])->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Pemeriksaan Fisik')
                            ->schema([
                                TextEntry::make('temperature')
                                    ->label('Suhu')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->temperature : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('blood_pressure')
                                    ->label('Tekanan darah')
                                    ->suffix('mm/Hg')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->blood_pressure : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('pulse')
                                    ->label('Nadi')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->pulse : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('respiratory')
                                    ->label('Frekuensi nafas')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->respiratory : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('weight')
                                    ->label('Berat Badan')
                                    ->suffix('kg')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->weight : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('height')
                                    ->label('Tinggi badan')
                                    ->suffix('cm')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->height : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                        'lg' => 1
                                    ]),
                                TextEntry::make('check_other')
                                    ->label('Pemeriksaan lainnya')
                                    ->getStateUsing(fn($record) => ($record->clientVisitCheck ? $record->clientVisitCheck->check_other : 0))
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->default("-")
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 2,
                                        'lg' => 2
                                    ]),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 4,
                                'lg' => 4,
                            ])
                            ->columnSpan(2),
                        Section::make('Diagnosa')
                            ->schema([
                                TextEntry::make('diagnose')
                                    ->label('')
                                    ->weight(FontWeight::Bold)
                                    ->default("-")
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                    ]
                ),
            ]);
    }
}
