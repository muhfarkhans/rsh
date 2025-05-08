<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\VisitResource;
use App\Forms\Components\PointSkeleton;
use App\Helpers\Helper;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use App\Models\ClientVisitCupping;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use Storage;
use Str;

class EditServiceVisit extends EditRecord
{
    protected static string $resource = VisitResource::class;

    protected Setting $setting;

    protected static ?string $title = 'Pengkajian';

    public function __construct()
    {
        $this->setting = Setting::where('id', 1)->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getFormActions(): array
    {
        if (
            in_array($this->record->status, [
                VisitStatus::REGISTER,
                VisitStatus::WAITING_FOR_CHECK,
                VisitStatus::WAITING_FOR_SERVICE,
                VisitStatus::ON_SERVICE,
            ])
        ) {
            return [
                $this->getSaveFormAction(),
                $this->getCancelFormAction()
            ];
        } else {
            return [];
        }
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['client_name'] = $this->record->client->name;
        $data['client_phone'] = $this->record->client->phone;
        $data['client_birthdate'] = $this->record->client->birthdate;
        $data['client_gender'] = $this->record->client->gender;
        $data['client_job'] = $this->record->client->job;
        $data['client_address'] = $this->record->client->address;
        $data['client_relation_as'] = $this->record->relation_as;

        if ($this->record->signature_therapist != null) {
            $data['signature_therapist'] = Helper::getFileAsBase64($this->record->signature_therapist);
        }

        if ($this->record->signature_client != null) {
            $data['signature_client'] = Helper::getFileAsBase64($this->record->signature_client);
        }

        if ($this->record->clientVisitCupping != null) {
            $data['service_id'] = $this->record->clientVisitCupping->service_id;
            $data['side_effect'] = $this->record->clientVisitCupping->side_effect;
            $data['first_action'] = $this->record->clientVisitCupping->first_action;
            $data['education_after'] = $this->record->clientVisitCupping->education_after;
            $data['subjective'] = $this->record->clientVisitCupping->subjective;
            $data['objective'] = $this->record->clientVisitCupping->objective;
            $data['analysis'] = $this->record->clientVisitCupping->analysis;
            $data['planning'] = $this->record->clientVisitCupping->planning;
            $data['points'] = $this->record->clientVisitCupping->points;
        }

        if ($this->record->clientVisitCheck) {
            $data['temperature'] = $this->record->clientVisitCheck->temperature;
            $data['sistolik'] = explode("-", $this->record->clientVisitCheck->blood_pressure)[0];
            $data['diastolik'] = explode("-", $this->record->clientVisitCheck->blood_pressure)[1];
            $data['pulse'] = $this->record->clientVisitCheck->pulse;
            $data['respiratory'] = $this->record->clientVisitCheck->respiratory;
            $data['height'] = $this->record->clientVisitCheck->height;
            $data['weight'] = $this->record->clientVisitCheck->weight;
            $data['checks_other'] = $this->record->clientVisitCheck->other[0];
        }

        $data['medical_history'] = $this->record->medical_history;
        $data['family_medical_history'] = $this->record->family_medical_history;
        $data['medication_history'] = $this->record->medication_history;
        $data['sleep_habits_start'] = $this->record->sleep_habits['start'];
        $data['sleep_habits_end'] = $this->record->sleep_habits['end'];
        $data['exercise_name'] = $this->record->exercise['name'];
        $data['exercise_intensity'] = $this->record->exercise['intensity'];
        $data['exercise_time'] = $this->record->exercise['time'];
        $data['nutrition_name'] = $this->record->nutrition['name'];
        $data['nutrition_portion'] = $this->record->nutrition['portion'];
        $data['nutrition_time'] = $this->record->nutrition['time'];
        $data['nutrition_type'] = $this->record->nutrition['type'];
        $data['spiritual_name'] = $this->record->spiritual['name'];
        $data['spiritual_type'] = $this->record->spiritual['type'];
        $data['check_temperature'] = $this->record->clientVisitCheck->temperature ?? 0;
        $data['check_blood_pressure'] = $this->record->clientVisitCheck->blood_pressure ?? 0;
        $data['check_pulse'] = $this->record->clientVisitCheck->pulse ?? 0;
        $data['check_respiratory'] = $this->record->clientVisitCheck->respiratory ?? 0;
        $data['check_weight'] = $this->record->clientVisitCheck->weight ?? 0 . " Kg";
        $data['check_height'] = $this->record->clientVisitCheck->height ?? 0 . " cm";
        $data['check_other'] = $this->record->clientVisitCheck->check_other ?? "";
        $data['diagnose'] = $this->record->diagnose;

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {

        $signatureTherapist = Helper::sanitizeBase64Image($data['signature_therapist']);
        $signatureTherapistFilename = Str::uuid() . '.png';
        Storage::disk('local')->put($signatureTherapistFilename, base64_decode($signatureTherapist));

        $signatureClient = Helper::sanitizeBase64Image($data['signature_client']);
        $signatureClientFilename = Str::uuid() . '.png';
        Storage::disk('local')->put($signatureClientFilename, base64_decode($signatureClient));

        return DB::transaction(function () use ($data, $record, $signatureTherapistFilename, $signatureClientFilename) {
            if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                $therapyId = $data['therapy_id'];
            } else {
                $therapyId = $record->therapy_id;
            }

            $dataCupping = [
                'client_visit_id' => $record->id,
                'therapy_id' => $therapyId,
                'service_id' => $data['service_id'],
                'side_effect' => $data['side_effect'],
                'first_action' => $data['first_action'],
                'education_after' => $data['education_after'],
                'subjective' => $data['subjective'],
                'objective' => $data['objective'],
                'analysis' => $data['analysis'],
                'planning' => $data['planning'],
                'points' => $data['points'] ?? null,
            ];

            if (isset($data['therapy_id'])) {
                $dataCupping['therapy_id'] = $therapyId;
            }

            if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                $dataCupping['therapy_id'] = $therapyId;
            }

            $cuppingExists = ClientVisitCupping::where('client_visit_id', $record->id)->first();
            if ($cuppingExists != null) {
                ClientVisitCupping::where('client_visit_id', $record->id)->update($dataCupping);
            } else {
                ClientVisitCupping::create($dataCupping);
            }

            $dataClientCheck = [
                'client_visit_id' => $record->id,
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['sistolik'] . "-" . $data['diastolik'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
                'weight' => $data['weight'],
                'height' => $data['height'],
                'other' => [
                    $data['checks_other']
                ],
            ];
            $checkExists = ClientVisitCheck::where('client_visit_id', $record->id)->first();
            if ($checkExists != null) {
                ClientVisitCheck::where('client_visit_id', $record->id)->update($dataClientCheck);
            } else {
                ClientVisitCheck::create($dataClientCheck);
            }

            // $totalTransaction = Transaction::count();
            // $service = Service::where('id', $data['service_id'])->first();

            // $additionalCupingPoint = 0;
            // if (isset($data['points'])) {
            //     if (is_array($data['points'])) {
            //         if (count($data['points']) >= 15) {
            //             $additionalCupingPoint = count($data['points']) - 15;
            //         }
            //     }
            // }

            // $additionalCuppingPointPrice = $this->setting->additional_cupping_price * $additionalCupingPoint;
            // $amount = $service->price + $additionalCuppingPointPrice;

            // $dataTransaction = [
            //     'client_visit_id' => $record->id,
            //     'created_by' => Auth::user()->id,
            //     'invoice_id' => "INV" . str_pad($totalTransaction + 1, 5, 0, STR_PAD_LEFT),
            //     'amount' => $amount,
            //     'total_discount' => 0,
            //     'payment_method' => PaymentMethod::WAITING_FOR_PAYMENT,
            //     'status' => TransactionStatus::WAITING_FOR_PAYMENT,
            // ];
            // $transactionExists = Transaction::where('client_visit_id', $record->id)->get();
            // if (count($transactionExists) > 0) {
            //     // Transaction::where('client_visit_id', $record->id)
            //     //     ->update(['status' => TransactionStatus::CANCEL]);

            //     Transaction::where('client_visit_id', $record->id)
            //         ->delete();
            // }

            // $createdTransaction = Transaction::create($dataTransaction);
            // $dataTransactionItem = [
            //     'transaction_id' => $createdTransaction->id,
            //     'service_id' => $service->id,
            //     'name' => $service->name,
            //     'qty' => 1,
            //     'price' => $service->price,
            //     'is_additional' => 0,
            // ];
            // TransactionItem::create($dataTransactionItem);

            // if ($additionalCupingPoint > 0) {
            //     $dataAdditionalTransactionItem = [
            //         'transaction_id' => $createdTransaction->id,
            //         'service_id' => $service->id,
            //         'name' => "Titik bekam tambahan (" . $service->name . ")",
            //         'qty' => $additionalCupingPoint,
            //         'price' => $additionalCuppingPointPrice,
            //         'is_additional' => 1,
            //     ];
            //     TransactionItem::create($dataAdditionalTransactionItem);
            // }

            $dataClientVisit = [
                'therapy_id' => $therapyId,
                'complaint' => $data['complaint'],
                'medical_history' => $data['medical_history'],
                'family_medical_history' => json_encode($data['family_medical_history']),
                'medication_history' => json_encode($data['medication_history']),
                'sleep_habits' => [
                    'start' => $data['sleep_habits_start'],
                    'end' => $data['sleep_habits_end'],
                ],
                'exercise' => [
                    'name' => $data['exercise_name'],
                    'intensity' => $data['exercise_intensity'],
                    'time' => $data['exercise_time'],
                ],
                'nutrition' => [
                    'name' => $data['nutrition_name'],
                    'portion' => $data['nutrition_portion'],
                    'time' => $data['nutrition_time'],
                    'type' => $data['nutrition_type'],
                ],
                'spiritual' => [
                    'name' => $data['spiritual_name'],
                    'type' => $data['spiritual_type'],
                ],
                'diagnose' => $data['diagnose'],
                'status' => VisitStatus::WAITING_FOR_SERVICE,
                'signature_therapist' => $signatureTherapistFilename,
                'signature_client' => $signatureClientFilename,
                'relation_as' => $data['client_relation_as'],
            ];

            if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                $dataClientVisit['therapy_id'] = $therapyId;
            }

            ClientVisit::where('id', $record->id)->update($dataClientVisit);

            if ($this->record->signature_therapist != null) {
                Helper::deleteFileStorage($this->record->signature_therapist);
            }

            if ($this->record->signature_client != null) {
                Helper::deleteFileStorage($this->record->signature_client);
            }

            return $record;
        });
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make()->columns(2)->schema([
                Grid::make()->columns(1)->schema([
                    Section::make()->schema([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('client_name')
                                ->label('Nama')
                                ->disabled(),
                            TextInput::make('client_phone')
                                ->numeric()
                                ->disabled()
                                ->label('No Telepon'),
                            DatePicker::make('client_birthdate')
                                ->label('Tanggal Lahir')
                                ->disabled(),
                            Select::make('client_gender')
                                ->label('Jenis kelamin')
                                ->disabled()
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ]),
                            TextInput::make('client_job')
                                ->label('Pekerjaan')
                                ->disabled()
                                ->columnSpan(2),
                            Textarea::make('client_address')
                                ->label('Alamat')
                                ->disabled()
                                ->columnSpan(2),
                        ])
                    ])->columnSpanFull(),
                    Section::make()->schema([
                        Select::make('service_id')
                            ->label('Nama Layanan')
                            ->options(function (): array {
                                return Service::get()
                                    ->mapWithKeys(function ($service) {
                                        $text = $service->name . ' - ' . $service->price . ' - ' . ($service->is_cupping ? 'Bekam' : 'Non-Bekam');
                                        return [$service->id => $text];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function (?string $state) {
                                if ($state == "1") {
                                    $this->hidePointSkeleton = true;
                                } else {
                                    $this->hidePointSkeleton = false;
                                }
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        Select::make('therapy_id')
                            ->label('Nama Terapis')
                            ->options(function () {
                                return User::with(['roles'])->whereHas('roles', function ($query) {
                                    return $query->where('name', Role::THERAPIST);
                                })->get()->pluck('name', 'id');
                            })
                            ->default(fn() => $this->record ? $this->record->therapy_id : 0)
                            ->live()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(function () {
                                if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('temperature')
                            ->label('Suhu')
                            ->required()
                            ->numeric()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('pulse')
                            ->label('Nadi')
                            ->default(1212)
                            ->required()
                            ->numeric()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('sistolik')
                            ->label('Sistolik')
                            ->required()
                            ->numeric()
                            ->suffix('mm/Hg')
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('diastolik')
                            ->label('Diastolik')
                            ->required()
                            ->numeric()
                            ->suffix('mm/Hg')
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('respiratory')
                            ->label('Frekuensi nafas')
                            ->required()
                            ->numeric()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 4,
                                'lg' => 4
                            ]),
                        TextInput::make('weight')
                            ->label('Berat Badan')
                            ->required()
                            ->numeric()
                            ->suffix('Kg')
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('height')
                            ->label('Tinggi badan')
                            ->required()
                            ->numeric()
                            ->suffix('cm')
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        Textarea::make('checks_other')
                            ->label('Pemeriksaan lainnya')
                            ->columnSpan([
                                'default' => 4,
                                'md' => 4,
                                'lg' => 4
                            ]),
                    ])->columnSpanFull(),
                ])
                    ->columns([
                        'default' => 1,
                        'md' => 4,
                        'lg' => 4,
                    ])->columnSpan(1),
                Grid::make()->columns(1)->schema([
                    Section::make()->schema([
                        PointSkeleton::make('points')
                            ->label('Tentukan Titik Bekam')
                            ->imageUrl("/assets/images/skeleton.jpg")
                            ->points([])
                            ->hidden(condition: function (Get $get) {
                                $id = $get('service_id');

                                if ($id == null) {
                                    return true;
                                }

                                $service = Service::where('id', $id)->first();

                                if ($service->is_cupping == 1) {
                                    return false;
                                }

                                return true;
                            })
                            ->live()
                            ->columnSpanFull(),
                        Placeholder::make('Pemberitahuan!')
                            ->content('Layanan yang anda pilih tidak termasuk terapi bekam')
                            ->hidden(condition: function (Get $get) {
                                $id = $get('service_id');

                                if ($id == null) {
                                    return true;
                                }

                                $service = Service::where('id', $id)->first();

                                if ($service->is_cupping == 1) {
                                    return true;
                                }

                                return false;
                            })
                            ->columnSpanFull(),
                        Placeholder::make('Pemberitahuan!')
                            ->content('Layanan belum dipilih')
                            ->hidden(condition: function (Get $get) {
                                $id = $get('service_id');

                                if ($id == null) {
                                    return false;
                                }

                                return true;
                            })
                            ->columnSpanFull(),
                        Section::make()->schema([
                            Placeholder::make('Titik bekam digunakan')
                                ->content(function (Get $get) {
                                    $limit = $this->setting->limit_cupping_point;
                                    $points = $get('points');
                                    $total = 0;
                                    if (is_array($points)) {
                                        $total = count($points);
                                    }

                                    return new HtmlString("<strong style=\"color: rgb(" . Color::Teal[500] . ")\">" . $total . "</strong> / " . $limit . " Kuota titik digunakan");
                                })
                                ->hidden(function (Get $get) {
                                    $id = $get('service_id');

                                    if ($id == null) {
                                        return true;
                                    }

                                    $service = Service::where('id', $id)->first();
                                    if ($service->is_cupping == 0) {
                                        return true;
                                    }

                                    return false;
                                })
                                ->columnSpanFull(),
                            Placeholder::make('Tambah titik bekam')
                                ->hint("1 Titik baru seharga " . number_format($this->setting->additional_cupping_price, 0, ',', '.') . " Rupiah")
                                ->content(function (Get $get) {
                                    $limit = $this->setting->limit_cupping_point;
                                    $points = $get('points');
                                    $additional = 0;
                                    if (is_array($points)) {
                                        if (count($points) >= $limit) {
                                            $additional = count($points) - $limit;
                                        }
                                    }

                                    return new HtmlString("<strong style=\"color: rgb(" . Color::Blue[500] . ")\">" . $additional . "</strong> Titik tambahan ditambahkan");
                                })
                                ->hidden(function (Get $get) {
                                    $limit = $this->setting->limit_cupping_point;
                                    $id = $get('service_id');

                                    if ($id == null) {
                                        return true;
                                    }

                                    $points = $get('points');
                                    if (is_array($points)) {
                                        if (count($points) >= $limit) {
                                            return false;
                                        }
                                    }

                                    return true;
                                })
                                ->columnSpanFull(),
                            Placeholder::make('Tambahan biaya')
                                ->content(function (Get $get) {
                                    $limit = $this->setting->limit_cupping_point;
                                    $points = $get('points');
                                    $additional = 0;
                                    if (is_array($points)) {
                                        if (count($points) >= $limit) {
                                            $additional = count($points) - $limit;
                                        }
                                    }

                                    $price = $additional * $this->setting->additional_cupping_price;
                                    return new HtmlString("<strong style=\"color: rgb(" . Color::Blue[500] . ")\">" . number_format($price, 0, ',', '.') . "</strong> Rupiah");
                                })
                                ->hidden(function (Get $get) {
                                    $limit = $this->setting->limit_cupping_point;
                                    $id = $get('service_id');

                                    if ($id == null) {
                                        return true;
                                    }

                                    $points = $get('points');
                                    if (is_array($points)) {
                                        if (count($points) >= $limit) {
                                            return false;
                                        }
                                    }

                                    return true;
                                })
                                ->columnSpanFull(),
                        ])->columns(2)->hidden(function (Get $get) {
                            $id = $get('service_id');

                            if ($id == null) {
                                return true;
                            }

                            return false;
                        })
                    ])->columnSpan(1)
                ])->columnSpan(1),
                Section::make('Riwayat Penyakit')
                    ->schema([
                        Textarea::make('complaint')
                            ->label('Keluhan yang dirasakan')
                            ->required()
                            ->columnSpan(2),
                        CheckboxList::make('medical_history')
                            ->label('Riwayat medis')
                            ->options([
                                'Diabetes Melitus' => 'Diabetes Melitus',
                                'Penyakit Jantung dan Penggunaan Alat Pacu Jantung' => 'Penyakit Jantung dan Penggunaan Alat Pacu Jantung',
                                'Kanker' => 'Kanker',
                                'Penyakit Darah' => 'Penyakit Darah',
                                'Gagal Organ' => 'Gagal Organ',
                                'Hepatitis' => 'Hepatitis',
                                'HIV/AIDS' => 'HIV/AIDS',
                                'Fraktur/Pembedahan' => 'Fraktur/Pembedahan',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->columns(3)
                            ->columnSpan(2),
                        Textarea::make('family_medical_history')
                            ->label('Riwayat penyakit keluarga')
                            ->columnSpan(2),
                        Textarea::make('medication_history')
                            ->label('Riwayat pengobatan')
                            ->columnSpan(2),
                        TimePicker::make('sleep_habits_start')
                            ->label('Waktu tidur')
                            ->hint('Gunakan format 24:00')
                            ->seconds(false)
                            ->columnSpan(1),
                        TimePicker::make('sleep_habits_end')
                            ->label('Waktu bangun')
                            ->hint('Gunakan format 24:00')
                            ->seconds(false)
                            ->columnSpan(1),
                        Grid::make()->columns(3)->schema([
                            TextInput::make('exercise_name')
                                ->label('Jenis olahraga'),
                            Select::make('exercise_intensity')
                                ->label('Intensitas olahraga')
                                ->options([
                                    'Ringan' => 'Ringan',
                                    'Sedang' => 'Sedang',
                                    'Berat' => 'Berat',
                                ]),
                            Select::make('exercise_time')
                                ->label('Waktu olahraga')
                                ->options([
                                    'Pagi' => 'Pagi',
                                    'Siang' => 'Siang',
                                    'Malam' => 'Malam',
                                ]),
                        ]),
                        Grid::make()->columns(2)->schema([
                            TextInput::make('nutrition_name')
                                ->label('Jenis makanan'),
                            Select::make('nutrition_portion')
                                ->label('Porsi makan')
                                ->options([
                                    'Sedikit' => 'Sedikit',
                                    'Sedang' => 'Sedang',
                                    'Banyak' => 'Banyak',
                                ]),
                            Select::make('nutrition_time')
                                ->label('Waktu makan')
                                ->options([
                                    'Pagi' => 'Pagi',
                                    'Siang' => 'Siang',
                                    'Malam' => 'Malam',
                                ]),
                            CheckboxList::make('nutrition_type')
                                ->label('Golongan makanan')
                                ->options([
                                    'Halal' => 'Halal',
                                    'Thoyyib' => 'Thoyyib',
                                    'Alami' => 'Alami',
                                ])
                                ->columns(3),
                        ]),
                        Grid::make()->columns(2)->schema([
                            MarkdownEditor::make('spiritual_name')
                                ->label('Ibadah wajib'),
                            CheckboxList::make('spiritual_type')
                                ->label('Jenis Ibadah')
                                ->options([
                                    'Sholat 5 waktu' => 'Sholat 5 waktu',
                                    'Membaca Alquran' => 'Membaca Alquran',
                                    'Shalat sunah' => 'Shalat sunah',
                                    'Puasa sunah' => 'Puasa sunah',
                                ]),
                        ]),
                        Textarea::make('diagnose')
                            ->label('Diagnosa')
                            ->required()
                            ->columnSpan(2),
                    ])->columns(2),
                Section::make()->schema([
                    Grid::make()->columns(1)->schema([
                        Textarea::make('side_effect')
                            ->label('Efek samping')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        Textarea::make('first_action')
                            ->label('Aksi pertama')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        Textarea::make('education_after')
                            ->label('Edukasi setelah tindakan')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('subjective')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('objective')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('analysis')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                        TextInput::make('planning')
                            ->required()
                            ->columnSpan([
                                'default' => 4,
                                'md' => 2,
                                'lg' => 2
                            ]),
                    ])
                ])
                    ->columns([
                        'default' => 1,
                        'md' => 4,
                        'lg' => 4,
                    ]),
                Section::make()->schema([
                    Grid::make()->columns(1)->schema([
                        TextInput::make('client_relation_as')
                            ->label('Hubungan dengan pasien')
                            ->default('Pasien')
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                    ])
                ])->columnSpanFull(),
                Section::make('Form Pernyataan')
                    ->description('Form pernyataan pasien bekam')
                    ->schema([
                        Placeholder::make('toc')
                            ->hiddenLabel()
                            ->content(function (Get $get) {
                                $serviceName = Service::where('id', $get('service_id'))->first()->name ?? "";
                                return new HtmlString(
                                    '<p><strong>' . $this->record->client->name . '<sup>1</sup></strong> dengan ini setuju untuk mendapatkan terapi bekam <strong>' . $serviceName . '<sup>2</sup></strong> untuk <strong>' . $this->record->client->name . '<sup>3</sup></strong>(<strong>' . $get('client_relation_as') . '<sup>4</sup></strong>) menyatakan bahwa : </p>
                                    <ul style="margin-left: 20px; margin-top: 20px">
                                        <li style="list-style-type: circle">Saya dengan sadar meminta untuk dilakukan Tindakan bekam.</li>
                                        <li style="list-style-type: circle">Saya memahami prosedur tindakan bekam yang akan dilakukan serta efek sampingnya.</li>
                                        <li style="list-style-type: circle">Informasi yang saya berikan kepada terapis bekam terkait keadaan kesehatan klien adalah benar adanya.</li>
                                        <li style="list-style-type: circle">Saya menyetujui pelaksanaan bekam dari saudara/i <strong>' . $this->record->createdBy->name . '</strong> dengan kesadaran penuh tanpa paksaan dari pihak manapun.</li>
                                    </ul>
                                    '
                                );
                            })->columnSpanFull(),
                        SignaturePad::make('signature_therapist')
                            ->label('TTE Terapis')
                            ->penColor('black')
                            ->required()
                            ->columns(1),
                        SignaturePad::make('signature_client')
                            ->label('TTE Pasien')
                            ->penColor('black')
                            ->required()
                            ->columns(1),
                        Placeholder::make('toc-notes')
                            ->hiddenLabel()
                            ->content(function (Get $get) {
                                return new HtmlString(
                                    '
                                    <div style="margin-left: 20px; margin-top: 20px">
                                        <p>1. Nama wali</p>
                                        <p>2. Jenis terapi bekam</p>
                                        <p>3. Nama pasien</p>
                                        <p>4. Hubungan dengan pasien</p>
                                    </div>
                                    '
                                );
                            })->columnSpanFull(),
                    ])->columns(2),
                Section::make('Pemberitahuan')
                    ->schema(
                        [
                            Placeholder::make('warning_1')
                                ->hiddenLabel()
                                ->content('Data tidak dapat diubah ketika layanan sudah dilakukan!')->columnSpanFull(),
                        ]
                    )
                    ->hidden(function () {
                        if (
                            in_array($this->record->status, [
                                VisitStatus::REGISTER,
                                VisitStatus::WAITING_FOR_CHECK,
                                VisitStatus::WAITING_FOR_SERVICE,
                                VisitStatus::ON_SERVICE,
                            ])
                        ) {
                            return true;
                        } else {
                            return false;
                        }
                    })
            ])
        ];
    }
}
