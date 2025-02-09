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

    protected static ?string $title = 'Edit Visit Service';

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
                VisitStatus::WAITING_FOR_SERVICE
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
            $data['blood_pressure'] = $this->record->clientVisitCheck->blood_pressure;
            $data['pulse'] = $this->record->clientVisitCheck->pulse;
            $data['respiratory'] = $this->record->clientVisitCheck->respiratory;
            $data['height'] = $this->record->clientVisitCheck->height;
            $data['weight'] = $this->record->clientVisitCheck->weight;
            $data['checks_other'] = $this->record->clientVisitCheck->other[0];
        }

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
            $dataCupping = [
                'client_visit_id' => $record->id,
                'therapy_id' => $data['therapy_id'],
                'service_id' => $data['service_id'],
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['blood_pressure'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
                'side_effect' => $data['side_effect'],
                'first_action' => $data['first_action'],
                'education_after' => $data['education_after'],
                'subjective' => $data['subjective'],
                'objective' => $data['objective'],
                'analysis' => $data['analysis'],
                'planning' => $data['planning'],
                'points' => $data['points'] ?? null,
            ];

            if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                $dataCupping['therapy_id'] = $data['therapy_id'];
            }

            $cuppingExists = ClientVisitCupping::where('client_visit_id', $record->id)->first();
            if ($cuppingExists) {
                $editCupping = ClientVisitCupping::where('client_visit_id', $record->id)->update($dataCupping);
            } else {
                $editCupping = ClientVisitCupping::create($dataCupping);
            }

            $dataClientCheck = [
                'client_visit_id' => $record->id,
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['blood_pressure'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
                'weight' => $data['weight'],
                'height' => $data['height'],
                'other' => [
                    $data['checks_other']
                ],
            ];
            $checkExists = ClientVisitCheck::where('client_visit_id', $record->id)->first();
            if ($cuppingExists) {
                ClientVisitCheck::where('client_visit_id', $record->id)->update($dataClientCheck);
            } else {
                ClientVisitCheck::create($dataClientCheck);
            }

            $totalTransaction = Transaction::count();
            $service = Service::where('id', $data['service_id'])->first();

            $additionalCupingPoint = 0;
            if (isset($data['points'])) {
                if (is_array($data['points'])) {
                    if (count($data['points']) > 14) {
                        $additionalCupingPoint = count($data['points']) - 14;
                    }
                }
            }

            $additionalCuppingPointPrice = $this->setting->additional_cupping_price * $additionalCupingPoint;
            $amount = $service->price + $additionalCuppingPointPrice;

            $dataClientVisit = [
                'signature_therapist' => $signatureTherapistFilename,
                'signature_client' => $signatureClientFilename,
                'relation_as' => $data['client_relation_as'],
            ];
            $dataTransaction = [
                'client_visit_id' => $record->id,
                'created_by' => Auth::user()->id,
                'invoice_id' => "INV" . str_pad($totalTransaction + 1, 5, 0, STR_PAD_LEFT),
                'amount' => $amount,
                'payment_method' => PaymentMethod::WAITING_FOR_PAYMENT,
                'status' => TransactionStatus::WAITING_FOR_PAYMENT,
            ];
            $transactionExists = Transaction::where('client_visit_id', $record->id)->first();
            if (!$transactionExists) {
                $createdTransaction = Transaction::create($dataTransaction);
                $dataTransactionItem = [
                    'transaction_id' => $createdTransaction->id,
                    'service_id' => $service->id,
                    'name' => $service->name,
                    'qty' => 1,
                    'price' => $service->price,
                    'is_additional' => 0,
                ];
                TransactionItem::create($dataTransactionItem);

                if ($additionalCupingPoint > 0) {
                    $dataAdditionalTransactionItem = [
                        'transaction_id' => $createdTransaction->id,
                        'service_id' => $service->id,
                        'name' => "Titik bekam tambahan (" . $service->name . ")",
                        'qty' => $additionalCupingPoint,
                        'price' => $additionalCuppingPointPrice,
                        'is_additional' => 1,
                    ];
                    TransactionItem::create($dataAdditionalTransactionItem);
                }

                $dataClientVisit['status'] = VisitStatus::WAITING_FOR_SERVICE;

                if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
                    $dataClientVisit['therapy_id'] = $data['therapy_id'];
                }
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
                        Grid::make()->columns(2)->schema([
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
                                ->columnSpanFull(),
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
                                ->columnSpanFull(),
                            TextInput::make('temperature')
                                ->label('Suhu')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->temperature : 0)
                                ->required()
                                ->numeric(),
                            TextInput::make('blood_pressure')
                                ->label('Tekanan darah')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->blood_pressure : 0)
                                ->required()
                                ->numeric()
                                ->suffix('mm/Hg'),
                            TextInput::make('pulse')
                                ->label('Nadi')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->pulse : 0)
                                ->required()
                                ->numeric(),
                            TextInput::make('respiratory')
                                ->label('Frekuensi nafas')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->respiratory : 0)
                                ->required()
                                ->numeric(),
                            TextInput::make('weight')
                                ->label('Berat Badan')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->weight : 0)
                                ->required()
                                ->numeric()
                                ->suffix('Kg'),
                            TextInput::make('height')
                                ->label('Tinggi badan')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->height : 0)
                                ->required()
                                ->numeric()
                                ->suffix('cm'),
                            MarkdownEditor::make('checks_other')
                                ->label('Pemeriksaan lainnya')
                                ->default(fn() => $this->record->clientVisitCheck ? $this->record->clientVisitCheck->check_other : 0)
                                ->columnSpan(2),
                        ])
                    ])->columnSpanFull(),
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
                ])
                    ->columnSpan(1),
                Section::make()->schema([
                    Grid::make()->columns(1)->schema([
                        MarkdownEditor::make('side_effect')
                            ->label('Efek samping')
                            ->required()
                            ->columnSpanFull(),
                        MarkdownEditor::make('first_action')
                            ->label('Aksi pertama')
                            ->required()
                            ->columnSpanFull(),
                        MarkdownEditor::make('education_after')
                            ->label('Edukasi setelah tindakan')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('subjective')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('objective')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('analysis')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('planning')
                            ->required()
                            ->columnSpanFull(),
                    ])
                ])->columnSpanFull(),
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
                                return new HtmlString(
                                    '<p><strong>' . $this->record->client->name . '<sup>1</sup></strong> dengan ini setuju untuk mendapatkan terapi bekam <strong>' . $get('service_id') . '<sup>2</sup></strong> untuk <strong>' . $this->record->client->name . '<sup>3</sup></strong>(<strong>' . $get('client_relation_as') . '<sup>4</sup></strong>) menyatakan bahwa : </p>
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
                            ->required()
                            ->columns(1),
                        SignaturePad::make('signature_client')
                            ->label('TTE Pasien')
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
                                VisitStatus::WAITING_FOR_SERVICE
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
