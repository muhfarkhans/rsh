<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Filament\App\Resources\CuppingResource;
use App\Models\ClientVisit;
use App\Models\ClientVisitCheck;
use App\Models\ClientVisitCupping;
use Filament\Actions;
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

class EditCupping extends EditRecord
{
    protected static string $resource = CuppingResource::class;

    public ?int $visitId = null;

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
                            ->required()
                            ->numeric(),
                        TextInput::make('blood_pressure')
                            ->label('Tekanan darah')
                            ->required()
                            ->numeric()
                            ->suffix('mm/Hg'),
                        TextInput::make('pulse')
                            ->label('Nadi')
                            ->required()
                            ->numeric(),
                        TextInput::make('respiratory')
                            ->label('Frekuensi nafas')
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
