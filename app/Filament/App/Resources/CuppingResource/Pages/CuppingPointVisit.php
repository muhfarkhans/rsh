<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Filament\App\Resources\CuppingResource;
use App\Models\ClientVisit;
use App\Models\ClientVisitCupping;
use Filament\Resources\Pages\Page;

class CuppingPointVisit extends Page
{
    protected static string $resource = CuppingResource::class;

    public ?int $visitId = null;

    public ClientVisitCupping $visitCupping;

    protected ClientVisit $clientVisit;

    public function mount($record): void
    {
        if ($record instanceof ClientVisitCupping) {
            $this->visitId = $record->client_visit_id;
            $this->visitCupping = $record;
        } else {
            $findData = ClientVisitCupping::where('id', $record)->first();
            $this->visitId = $findData->client_visit_id;
            $this->visitCupping = $findData;
        }
    }

    protected static string $view = 'filament.app.resources.cupping-resource.pages.cupping-point-visit';

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
            if (isset($snapshot->data->visitId)) {
                return $snapshot->data->visitId;
            } else {
                $pathString = $snapshot->memo->path;
                return explode("/", $pathString)[count(explode("/", $pathString)) - 2];
            }
        }

        return explode("/", request()->url())[count(explode("/", request()->url())) - 2];
    }
}
