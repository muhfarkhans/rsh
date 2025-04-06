<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Filament\App\Resources\CuppingResource;
use App\Helpers\Helper;
use App\Models\ClientVisit;
use App\Models\ClientVisitCupping;
use Filament\Resources\Pages\Page;

class CuppingPointVisit extends Page
{
    protected static string $resource = CuppingResource::class;

    public ?int $visitId = null;

    public ClientVisitCupping $visitCupping;

    public array $data;

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

        $this->clientVisit = ClientVisit::where('id', $this->visitCupping->client_visit_id)->first();

        $this->data = [
            'id' => $this->clientVisit->id,
            'client_reg_id' => $this->clientVisit->client->reg_id,
            'transaction_invoice_id' => $this->clientVisit->transactions->last()->invoice_id ?? '',
            'client_name' => $this->clientVisit->client->name,
            'client_phone' => $this->clientVisit->client->phone,
            'client_gender' => $this->clientVisit->client->gender,
            'client_year' => $this->clientVisit->client->birthdate,
            'job' => $this->clientVisit->client->job,
            'address' => $this->clientVisit->client->address,
            'visit_complaint' => $this->clientVisit->complaint,
            'visit_medical_history' => $this->clientVisit->medical_history,
            'visit_family_medical_history' => $this->clientVisit->family_medical_history,
            'visit_medication_history' => $this->clientVisit->medication_history,
            'visit_sleep_start' => $this->clientVisit->sleep_habits['start'],
            'visit_sleep_end' => $this->clientVisit->sleep_habits['end'],
            'visit_exercise_name' => $this->clientVisit->exercise['name'],
            'visit_exercise_intensity' => $this->clientVisit->exercise['intensity'],
            'visit_exercise_time' => $this->clientVisit->exercise['time'],
            'visit_nutrition_name' => $this->clientVisit->nutrition['name'],
            'visit_nutrition_portion' => $this->clientVisit->nutrition['portion'],
            'visit_nutrition_time' => $this->clientVisit->nutrition['time'],
            'visit_nutrition_type' => is_array($this->clientVisit->nutrition['type']) ? $this->clientVisit->nutrition['type'] : [],
            'visit_spiritual_name' => $this->clientVisit->spiritual['name'],
            'visit_spiritual_type' => is_array($this->clientVisit->spiritual['type']) ? $this->clientVisit->spiritual['type'] : [],
            'visit_check_temperature' => $this->clientVisit->clientVisitCheck->temperature ?? 0,
            'visit_check_blood_pressure' => $this->clientVisit->clientVisitCheck->blood_pressure ?? 0,
            'visit_check_pulse' => $this->clientVisit->clientVisitCheck->pulse ?? 0,
            'visit_check_respiratory' => $this->clientVisit->clientVisitCheck->respiratory ?? 0,
            'visit_check_weight' => $this->clientVisit->clientVisitCheck->weight ?? 0 . " Kg",
            'visit_check_height' => $this->clientVisit->clientVisitCheck->height ?? 0 . " cm",
            'visit_check_other' => $this->clientVisit->clientVisitCheck->other ?? "",
            'visit_diagnose' => $this->clientVisit->diagnose,
            'service_name' => $this->clientVisit->clientVisitCupping->service->name,
            'service_price' => $this->clientVisit->clientVisitCupping->service->price,
            'service_therapist' => $this->clientVisit->clientVisitCupping->therapist->name,
            'client_name_related' => $this->clientVisit->relation_as,
            'signature_therapist' => '',
            'signature_client' => '',
        ];

        if ($this->clientVisit->signature_therapist != '') {
            $this->data['signature_therapist'] = Helper::getFileAsBase64($this->clientVisit->signature_therapist);
        }

        if ($this->clientVisit->signature_client != '') {
            $this->data['signature_client'] = Helper::getFileAsBase64($this->clientVisit->signature_client);
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
