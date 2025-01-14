<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Filament\App\Resources\VisitResource;
use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
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
use Illuminate\Support\Str;


class CreateVisit extends CreateRecord
{
    use HasWizard;

    protected static string $resource = VisitResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $totalClient = Client::count();
            $dataClient = [
                'reg_id' => str_pad($totalClient + 1, 5, 0, STR_PAD_LEFT),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'birthdate' => $data['birthdate'],
                'gender' => $data['gender'],
                'job' => $data['job'],
                'address' => $data['address'],
            ];
            $createdClient = Client::create($dataClient);

            $dataClientVisit = [
                'client_id' => $createdClient->id,
                'created_by' => Auth::user()->id,
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
                'diagnose' => $data['diagnose'],
            ];
            $createdClientVisit = ClientVisit::create($dataClientVisit);

            $dataClientCheck = [
                'client_visit_id' => $createdClientVisit->id,
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['blood_pressure'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
                'weight' => $data['weight'],
                'height' => $data['height'],
                'other' => $data['checks_other'],
            ];
            $createdClientCheck = ClientVisitCheck::create($dataClientCheck);

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

            Notification::make()
                ->title('Client found')
                ->success()
                ->body('Form is automatically filled.')
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Client not found')
                ->warning()
                ->body('Please fill the form manual.')
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
                        ])
                    ]),
                    TextInput::make('name')
                        ->label('Nama'),
                    TextInput::make('phone')
                        ->numeric()
                        ->label('No Telepon'),
                    DatePicker::make('birthdate')
                        ->label('Tanggal Lahir'),
                    Select::make('gender')
                        ->label('Jenis kelamin')
                        ->options([
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan',
                        ]),
                    TextInput::make('job')
                        ->label('Pekerjaan')
                        ->columnSpan(2),
                    Textarea::make('address')
                        ->label('Alamat')
                        ->columnSpan(2),
                ])->columns(2),
            Step::make('Riwayat Penyakit')
                ->schema([
                    MarkdownEditor::make('complaint')
                        ->label('Keluhan yang dirasakan')
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
            Step::make('Pemeriksaan Fisik')
                ->schema([
                    TextInput::make('temperature')
                        ->label('Suhu'),
                    TextInput::make('blood_pressure')
                        ->label('Tekanan darah')
                        ->suffix('mm/Hg'),
                    TextInput::make('pulse')
                        ->label('Nadi'),
                    TextInput::make('respiratory')
                        ->label('Frekuensi nafas'),
                    TextInput::make('weight')
                        ->label('Berat Badan')
                        ->suffix('Kg'),
                    TextInput::make('height')
                        ->label('Tinggi badan')
                        ->suffix('cm'),
                    MarkdownEditor::make('checks_other')
                        ->label('Pemeriksaan lainnya')
                        ->columnSpan(2),
                ])->columns(2),
            Step::make('Diagnosa')
                ->schema([
                    MarkdownEditor::make('diagnose')
                        ->label('Diagnosa')
                        ->columnSpan(2),
                ]),
        ];
    }
}
