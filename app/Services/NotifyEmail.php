<?php

namespace App\Services;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Jobs\EmailNewVisitJob;
use App\Models\User;
use DB;

class NotifyEmail
{
    public function sendEmail($record)
    {
        $emailPayload = [
            'client_reg_id' => $record->client->reg_id,
            'client_name' => $record->client->name,
            'client_service' => $record->clientVisitCupping->service->name,
            'client_service_price' => $record->clientVisitCupping->service->price,
            'client_service_commision' => $record->clientVisitCupping->service->commision,
            'client_service_is_cupping' => $record->clientVisitCupping->service->is_cupping,
            'client_service_started_at' => $record->started_at,
            'client_service_finished_at' => now(),
            'client_service_status' => VisitStatus::WAITING_FOR_PAYMENT,
            'client_therapist' => $record->therapy->name,
            'client_created_at' => $record->created_at,
        ];

        $idSuperAdmin = DB::table('roles')->where('name', Role::SUPER_ADMIN)->first()->id;
        $users = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.role_id', $idSuperAdmin)
            ->where('users.is_active', 1)
            ->get();

        foreach ($users as $key => $admin) {
            dispatch(new EmailNewVisitJob($emailPayload, $admin->email));
        }

    }
}