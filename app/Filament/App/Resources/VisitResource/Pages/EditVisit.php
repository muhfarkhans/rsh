<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role;
use App\Filament\App\Resources\VisitResource;
use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EditVisit extends EditRecord
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->client->name;
        $data['phone'] = $this->record->client->phone;
        $data['birthdate'] = $this->record->client->birthdate;
        $data['year'] = date('Y', strtotime($this->record->client->birthdate));
        $data['gender'] = $this->record->client->gender;
        $data['job'] = $this->record->client->job;
        $data['address'] = $this->record->client->address;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($data, $record) {
            $startOfYear = Carbon::create($data['year'], 1, 1)->startOfYear();

            $dataClient = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'birthdate' => $startOfYear,
                'gender' => $data['gender'],
                'job' => $data['job'],
                'address' => $data['address'],
            ];
            Client::where('id', $record->client_id)
                ->update($dataClient);

            ClientVisit::where('id', $record->id)->update([
                'therapy_id' => $data['therapy_id']
            ]);

            return ClientVisit::where('id', $record->id)->first();
        });
    }

    public function getFormSchema(): array
    {
        return [
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
                ->label('Jenis Kelamin')
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
        ];
    }
}
