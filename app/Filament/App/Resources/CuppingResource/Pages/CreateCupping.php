<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Filament\App\Resources\CuppingResource;
use App\Filament\App\Resources\VisitResource;
use App\Forms\Components\PointSkeleton;
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
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CreateCupping extends CreateRecord
{
    public function __construct()
    {
        $id = $this->getVisitIdFromUrl();
        $this->clientVisit = ClientVisit::where('id', $id)->first();
    }

    protected static string $resource = CuppingResource::class;

    protected static bool $canCreateAnother = false;

    protected static bool $canCancel = false;

    public ?int $visitId = null;

    protected ClientVisit $clientVisit;

    public function mount(?int $visit = null): void
    {
        parent::mount();

        $this->visitId = $visit;
        $this->clientVisit = ClientVisit::where('id', $visit)->first();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $dataCupping = [
                'client_visit_id' => $this->visitId,
                'therapy_id' => Auth::user()->id,
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
                'points' => $data['points'],
            ];
            $createdCupping = ClientVisitCupping::create($dataCupping);

            ClientVisitCheck::where('client_visit_id', $this->visitId)->update([
                'temperature' => $data['temperature'],
                'blood_pressure' => $data['blood_pressure'],
                'pulse' => $data['pulse'],
                'respiratory' => $data['respiratory'],
            ]);

            $totalTransaction = Transaction::count();
            $service = Service::where('id', $data['service_id'])->first();
            $dataTransaction = [
                'client_visit_id' => $this->visitId,
                'created_by' => Auth::user()->id,
                'invoice_id' => "INV" . str_pad($totalTransaction + 1, 5, 0, STR_PAD_LEFT),
                'amount' => $service->price,
                'payment_method' => PaymentMethod::WAITING_FOR_PAYMENT,
                'status' => TransactionStatus::WAITING_FOR_PAYMENT,
            ];
            $createdTransaction = Transaction::create($dataTransaction);

            $dataTransactionItem = [
                'transaction_id' => $createdTransaction->id,
                'service_id' => $service->id,
                'name' => $service->name,
                'qty' => 1,
                'price' => $service->price,
            ];
            $createdTransactionItem = TransactionItem::create($dataTransactionItem);

            return $createdCupping;
        });
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.app.resources.visits.index') => 'Client Visits',
            route('filament.app.resources.visits.view', ['record' => $this->visitId]) => 'View',
            '' => 'Create Cupping',
        ];
    }

    protected function getVisitIdFromUrl(): mixed
    {
        if (request()->getContent() != null) {
            $content = json_decode(request()->getContent());
            $snapshot = json_decode($content->components[0]->snapshot);
            return $snapshot->data->visitId;
        }

        return explode("/", request()->url())[count(explode("/", request()->url())) - 2];
    }

    protected function getRedirectUrl(): string
    {
        return VisitResource::getUrl('view', ['record' => $this->visitId]);
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make()->columns(2)->schema([
                Grid::make()->columns(1)->schema([
                    Section::make()->schema([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->default(fn() => $this->clientVisit->client->name)
                                ->disabled(),
                            TextInput::make('phone')
                                ->numeric()
                                ->default(fn() => $this->clientVisit->client->phone)
                                ->disabled()
                                ->label('No Telepon'),
                            DatePicker::make('birthdate')
                                ->label('Tanggal Lahir')
                                ->default(fn() => $this->clientVisit->client->birthdate)
                                ->disabled(),
                            Select::make('gender')
                                ->label('Jenis kelamin')
                                ->default(fn() => $this->clientVisit->client->gender)
                                ->disabled()
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ]),
                            TextInput::make('job')
                                ->label('Pekerjaan')
                                ->default(fn() => $this->clientVisit->client->job)
                                ->disabled()
                                ->columnSpan(2),
                            Textarea::make('address')
                                ->label('Alamat')
                                ->default(fn() => $this->clientVisit->client->address)
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
                                ->columnSpanFull(),
                            TextInput::make('temperature')
                                ->label('Suhu')
                                ->default(fn() => $this->clientVisit->clientVisitCheck->temperature)
                                ->required()
                                ->numeric(),
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
                            ->content(function (Get $get) {
                                return new HtmlString(
                                    '<p><strong>' . $this->clientVisit->client->name . '<sup>1</sup></strong> dengan ini setuju untuk mendapatkan terapi bekam <strong>' . $get('cupping_type') . '<sup>2</sup></strong> untuk <strong>' . $this->clientVisit->client->name . '<sup>3</sup></strong>(<strong>' . $get('relationship_client') . '<sup>4</sup></strong>) menyatakan bahwa : </p>
                                    <ul style="margin-left: 20px; margin-top: 20px">
                                        <li style="list-style-type: circle">Saya dengan sadar meminta untuk dilakukan Tindakan bekam.</li>
                                        <li style="list-style-type: circle">Saya memahami prosedur tindakan bekam yang akan dilakukan serta efek sampingnya.</li>
                                        <li style="list-style-type: circle">Informasi yang saya berikan kepada terapis bekam terkait keadaan kesehatan klien adalah benar adanya.</li>
                                        <li style="list-style-type: circle">Saya menyetujui pelaksanaan bekam dari saudara/i <strong>' . $this->clientVisit->createdBy->name . '</strong> dengan kesadaran penuh tanpa paksaan dari pihak manapun.</li>
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
