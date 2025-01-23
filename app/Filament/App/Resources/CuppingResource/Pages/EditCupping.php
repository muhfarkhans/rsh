<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Constants\TransactionStatus;
use App\Filament\App\Resources\CuppingResource;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use App\Models\ClientVisitCupping;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Forms\Components\PointSkeleton;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class EditCupping extends EditRecord
{
    public function __construct()
    {
        //
    }

    protected static string $resource = CuppingResource::class;

    public ?int $visitId = null;

    protected ClientVisit $clientVisit;

    public function mount($record): void
    {
        parent::mount($record);

        if ($record instanceof ClientVisitCupping) {
            $this->visitId = $record->client_visit_id;
        } else {
            $record = ClientVisitCupping::find($record);
            $this->visitId = $record->client_visit_id;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($data, $record) {
            if ($data['service_id'] != $record->service_id) {
                $service = Service::where('id', $data['service_id'])->first();
                $transaction = Transaction::where('id', $record->client_visit_id)->first();
                Transaction::where('id', $record->client_visit_id)->update(['amount' => $service->price]);
                TransactionItem::where('transaction_id', $transaction->id)->delete();

                $dataTransactionItem = [
                    'transaction_id' => $transaction->id,
                    'service_id' => $service->id,
                    'name' => $service->name,
                    'qty' => 1,
                    'price' => $service->price,
                ];
                $createdTransactionItem = TransactionItem::create($dataTransactionItem);
            }

            $record->update($data);

            ClientVisitCheck::where('client_visit_id', $this->visitId)->update([
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['blood_pressure'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
            ]);

            return $record;
        });
    }

    protected function getVisitIdFromUrl(): mixed
    {
        if (request()->getContent() != null) {
            $content = json_decode(request()->getContent());
            $snapshot = json_decode($content->components[0]->snapshot);
            if (isset($snapshot->data->visitId)) {
                return $snapshot->data->visitId;
            } else {
                $pathString = $snapshot->memo->path;
                return explode("/", $pathString)[count(explode("/", $pathString)) - 2];
            }
        }

        return explode("/", request()->url())[count(explode("/", request()->url())) - 2];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.app.resources.visits.index') => 'Client Visits',
            route('filament.app.resources.visits.view', ['record' => $this->visitId]) => 'View',
            '' => 'Create Cupping',
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->clientVisit = ClientVisit::with(['client'])
            ->whereHas('clientVisitCupping', function ($query) use ($data) {
                return $query->where('id', $data['id']);
            })
            ->first();

        $data['name'] = $this->clientVisit->client->name;
        $data['phone'] = $this->clientVisit->client->phone;
        $data['birthdate'] = $this->clientVisit->client->birthdate;
        $data['gender'] = $this->clientVisit->client->gender;
        $data['job'] = $this->clientVisit->client->job;
        $data['address'] = $this->clientVisit->client->address;

        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('id')
                ->readOnly()
                ->hidden(),
            Grid::make()->columns(2)->schema([
                Grid::make()->columns(1)->schema([
                    Section::make()->schema([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->disabled(),
                            TextInput::make('phone')
                                ->numeric()
                                ->disabled()
                                ->label('No Telepon'),
                            DatePicker::make('birthdate')
                                ->label('Tanggal Lahir')
                                ->disabled(),
                            Select::make('gender')
                                ->label('Jenis kelamin')
                                ->disabled()
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ]),
                            TextInput::make('job')
                                ->label('Pekerjaan')
                                ->disabled()
                                ->columnSpan(2),
                            Textarea::make('address')
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
                                            return [$service->id => $service->name . ' - ' . $service->price];
                                        })
                                        ->toArray();
                                })
                                ->live()
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disableOptionWhen(function (string $value, Model $record) {
                                    $transaction = optional($record->clientVisit)->transaction;

                                    return $transaction && $transaction->status === TransactionStatus::PAID;
                                })
                                ->columnSpanFull(),
                            TextInput::make('temperature')
                                ->label('Suhu')
                                ->default(fn() => $this->clientVisit->client->temperature)
                                ->required(),
                            TextInput::make('blood_pressure')
                                ->label('Tekanan darah')
                                ->default(fn() => $this->clientVisit->clientVisitCheck->blood_pressure)
                                ->required()
                                ->numeric()
                                ->suffix('mm/Hg'),
                            TextInput::make('pulse')
                                ->label('Nadi')
                                ->default(fn() => $this->clientVisit->clientVisitCheck->pulse)
                                ->required()
                                ->numeric(),
                            TextInput::make('respiratory')
                                ->label('Frekuensi nafas')
                                ->default(fn() => $this->clientVisit->clientVisitCheck->respiratory)
                                ->required()
                                ->numeric(),
                        ])
                    ])->columnSpanFull(),
                ])->columnSpan(1),
                Grid::make()->columns(1)->schema([
                    Section::make()->schema([
                        PointSkeleton::make('points')
                            ->label('Titik bekam')
                            ->imageUrl("/assets/images/skeleton.jpg")
                            ->points([])
                            ->required()
                            ->columnSpanFull(),
                    ])->columnSpan(1)
                ])->columnSpan(1),
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
                        TextInput::make('relationship_client')
                            ->label('Hubungan dengan pasien')
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
                            ->content(function (ClientVisitCupping $record, Get $get) {
                                return new HtmlString(
                                    '<p><strong>' . $record->clientVisit->client->name . '<sup>1</sup></strong> dengan ini setuju untuk mendapatkan terapi bekam <strong>' . $get('cupping_type') . '<sup>2</sup></strong> untuk <strong>' . $record->clientVisit->client->name . '<sup>3</sup></strong>(<strong>' . $get('relationship_client') . '<sup>4</sup></strong>) menyatakan bahwa : </p>
                                    <ul style="margin-left: 20px; margin-top: 20px">
                                        <li style="list-style-type: circle">Saya dengan sadar meminta untuk dilakukan Tindakan bekam.</li>
                                        <li style="list-style-type: circle">Saya memahami prosedur tindakan bekam yang akan dilakukan serta efek sampingnya.</li>
                                        <li style="list-style-type: circle">Informasi yang saya berikan kepada terapis bekam terkait keadaan kesehatan klien adalah benar adanya.</li>
                                        <li style="list-style-type: circle">Saya menyetujui pelaksanaan bekam dari saudara/i <strong>' . $record->clientVisit->createdBy->name . '</strong> dengan kesadaran penuh tanpa paksaan dari pihak manapun.</li>
                                    </ul>
                                    '
                                );
                            }),
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
                            }),
                    ])
            ])
        ];
    }
}
