<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Filament\App\Resources\CuppingResource;
use App\Filament\App\Resources\VisitResource;
use App\Forms\Components\PointSkeleton;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use App\Models\ClientVisitCupping;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                'cupping_type' => $data['cupping_type'],
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
                Section::make()->schema([
                    Grid::make()->columns(2)->schema([
                        Select::make('cupping_type')
                            ->label('Jenis bekam')
                            ->required()
                            ->options([
                                'Bekam basah' => 'Bekam basah',
                                'Bekam kering' => 'Bekam kering',
                                'Lainnya' => 'Lainnya',
                            ])
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
                ])->columnSpan(1),
                Section::make()->schema([
                    PointSkeleton::make('points')
                        ->label('Titik bekam')
                        ->imageUrl("/assets/images/skeleton.jpg")
                        ->points([])
                        ->required()
                        ->columnSpanFull(),
                ])->columnSpan(1)
            ])
        ];
    }
}