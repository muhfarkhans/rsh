<?php

namespace App\Http\Controllers;

use App\Exports\PayrollExport;
use App\Helpers\Helper;
use App\Models\ClientVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel;

class ExportController extends Controller
{
    public function detailPdf(Request $request)
    {
        $clientVisit = ClientVisit::where('id', $request->input('id'))->first();
        $mapBase64 = $request->input('map_base64');

        $data = [
            'id' => $clientVisit->id,
            'client_reg_id' => $clientVisit->client->reg_id,
            'transaction_invoice_id' => $clientVisit->transactions->last()->invoice_id ?? '',
            'client_name' => $clientVisit->client->name,
            'client_phone' => $clientVisit->client->phone,
            'client_gender' => $clientVisit->client->gender,
            'client_year' => $clientVisit->client->birthdate,
            'job' => $clientVisit->client->job,
            'address' => $clientVisit->client->address,
            'visit_complaint' => $clientVisit->complaint,
            'visit_medical_history' => $clientVisit->medical_history,
            'visit_family_medical_history' => $clientVisit->family_medical_history,
            'visit_medication_history' => $clientVisit->medication_history,
            'visit_sleep_start' => $clientVisit->sleep_habits['start'],
            'visit_sleep_end' => $clientVisit->sleep_habits['end'],
            'visit_exercise_name' => $clientVisit->exercise['name'],
            'visit_exercise_intensity' => $clientVisit->exercise['intensity'],
            'visit_exercise_time' => $clientVisit->exercise['time'],
            'visit_nutrition_name' => $clientVisit->nutrition['name'],
            'visit_nutrition_portion' => $clientVisit->nutrition['portion'],
            'visit_nutrition_time' => $clientVisit->nutrition['time'],
            'visit_nutrition_type' => is_array($clientVisit->nutrition['type']) ? $clientVisit->nutrition['type'] : [],
            'visit_spiritual_name' => $clientVisit->spiritual['name'],
            'visit_spiritual_type' => is_array($clientVisit->spiritual['type']) ? $clientVisit->spiritual['type'] : [],
            'visit_check_temperature' => $clientVisit->clientVisitCheck->temperature ?? 0,
            'visit_check_blood_pressure' => $clientVisit->clientVisitCheck->blood_pressure ?? 0,
            'visit_check_pulse' => $clientVisit->clientVisitCheck->pulse ?? 0,
            'visit_check_respiratory' => $clientVisit->clientVisitCheck->respiratory ?? 0,
            'visit_check_weight' => $clientVisit->clientVisitCheck->weight ?? 0 . " Kg",
            'visit_check_height' => $clientVisit->clientVisitCheck->height ?? 0 . " cm",
            'visit_check_other' => $clientVisit->clientVisitCheck->other ?? "",
            'visit_diagnose' => $clientVisit->diagnose,
            'service_name' => $clientVisit->clientVisitCupping->service->name,
            'service_price' => $clientVisit->clientVisitCupping->service->price,
            'service_therapist' => $clientVisit->clientVisitCupping->therapist->name,
            'client_name_related' => $clientVisit->relation_as,
            'signature_therapist' => '',
            'signature_client' => '',
            'map_base64' => $mapBase64,
        ];

        if ($clientVisit->signature_therapist != '') {
            $data['signature_therapist'] = Helper::getFileAsBase64($clientVisit->signature_therapist);
        }

        if ($clientVisit->signature_client != '') {
            $data['signature_client'] = Helper::getFileAsBase64($clientVisit->signature_client);
        }

        $pdf = Pdf::loadView('pdf.detail', ['data' => $data]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'detail.pdf');
    }

    public function exportPayroll(Request $request)
    {
        $createdFrom = $request->get('created_from');
        $createdUntil = $request->get('created_until');
        $search = $request->get('search');

        $filename = 'payroll-' . $createdFrom . '-' . $createdUntil . '.xlsx';

        return (new PayrollExport($createdFrom, $createdUntil, $search))
            ->download($filename);
    }
}
