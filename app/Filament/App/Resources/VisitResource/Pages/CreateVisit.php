<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\VisitResource;
use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;


class CreateVisit extends CreateRecord
{
    use HasWizard;

    protected static string $resource = VisitResource::class;

    protected $clientVisitStats = [
        'total' => 0,
        'last_date' => '-',
    ];

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $totalClient = Client::count();
            $clientExists = Client::where('reg_id', $data['reg_id'])->first();
            $startOfYear = Carbon::create($data['year'], 1, 1)->startOfYear();

            if ($clientExists == null) {
                $dataClient = [
                    'reg_id' => str_pad($totalClient + 1, 5, 0, STR_PAD_LEFT),
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'birthdate' => $startOfYear,
                    'gender' => $data['gender'],
                    'job' => $data['job'],
                    'address' => $data['address'],
                ];
                $createdClient = Client::create($dataClient);
            } else {
                $createdClient = $clientExists;
            }

            $dataClientVisit = [
                'client_id' => $createdClient->id,
                'created_by' => Auth::user()->id,
                'therapy_id' => $data['therapy_id'],
                'complaint' => $data['complaint'],
                'medical_history' => $data['medical_history'],
                'family_medical_history' => $data['family_medical_history'],
                'medication_history' => $data['medication_history'],
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
                'diagnose' => "-",
                'status' => VisitStatus::WAITING_FOR_CHECK,
            ];
            $createdClientVisit = ClientVisit::create($dataClientVisit);

            return $createdClientVisit;
        });
    }

    protected function serachRegId(string|null $regId, Set $set)
    {
        try {
            $client = Client::where('reg_id', $regId)->firstOrFail();

            $set('name', $client->name);
            $set('phone', $client->phone);
            $set('birthdate', $client->birthdate);
            $set('gender', $client->gender);
            $set('job', $client->job);
            $set('address', $client->address);
            $set('client_found', 1);

            $clientVisit = ClientVisit::where('client_id', $client->id)->orderBy('created_at', 'desc')->get();

            if (count($clientVisit) > 0) {
                $liLastDate = '';
                for ($i = 0; $i < 5; $i++) {
                    if (isset($clientVisit[$i])) {
                        $liLastDate .= '<li><span style="font-weight: bold">' . $clientVisit[$i]->created_at . '</span> ' . Carbon::parse($clientVisit[$i]->created_at)->diffForHumans() . '</li>';
                    }
                }
                $this->clientVisitStats = [
                    'total' => count($clientVisit),
                    'last_date' => $liLastDate,
                ];
            } else {
                $this->clientVisitStats = [
                    'total' => count($clientVisit),
                    'last_date' => '-',
                ];
            }

            Notification::make()
                ->title('Client ditemukan')
                ->success()
                ->body('Form berhasil diisi otomatis.')
                ->send();
        } catch (\Throwable $th) {
            $set('client_found', 0);
            $this->clientVisitStats = [
                'total' => count($clientVisit),
                'last_date' => '-',
            ];

            Notification::make()
                ->title('Client tidak ditemukan')
                ->warning()
                ->body('Mohon isi form secara manual.')
                ->send();
        }
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Data diri')
                ->schema([
                    Grid::make()->columns(1)->schema([
                        TextInput::make('reg_id')
                            ->label('Nomor Registrasi')
                            ->columnSpan(2),
                        \Filament\Forms\Components\Actions::make([
                            Action::make('Generate excerpt')
                                ->label('Cari')
                                ->action(function (Get $get, Set $set) {
                                    $this->serachRegId($get('reg_id'), $set);
                                })
                        ]),
                    ]),
                    Toggle::make('client_found')->hidden(),
                    Section::make('Client Info')
                        ->hidden(function (Get $get, Set $set) {
                            if ($get('client_found') == 1) {
                                return false;
                            }

                            return true;
                        })
                        ->schema([
                            Placeholder::make('total_visit')
                                ->label('Jumlah Kunjungan')
                                ->content(function () {
                                    return $this->clientVisitStats['total'] . " x";
                                })
                                ->columnSpanFull(),
                            Placeholder::make('last_visit')
                                ->label('Tanggal Kunjungan Terkahir')
                                ->content(function () {
                                    return new HtmlString('
                                        <ol style="list-style-type: decimal; margin-left: 30px">' . $this->clientVisitStats['last_date'] . '</ol>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),
                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),
                    TextInput::make('phone')
                        ->numeric()
                        ->required()
                        ->default('62')
                        ->regex('/^62[0-9]{9,15}$/')
                        ->label('No Telepon'),
                    TextInput::make('year')
                        ->label('Tahun Lahir')
                        ->numeric()
                        ->minValue(1960)
                        ->maxValue(2025),
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
                                return $query->where('name', Role::THERAPIST);
                            })->get()->pluck('name', 'id');
                        })
                        ->live()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ])->columns(2),
            Step::make('Riwayat Penyakit')
                ->schema([
                    MarkdownEditor::make('complaint')
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
                    MarkdownEditor::make('family_medical_history')
                        ->label('Riwayat penyakit keluarga')
                        ->columnSpan(2),
                    MarkdownEditor::make('medication_history')
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
                    ])
                ])->columns(2),
        ];
    }
}
