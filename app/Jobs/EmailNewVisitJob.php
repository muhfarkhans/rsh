<?php

namespace App\Jobs;

use App\Mail\EmailNewVisit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Mail;

class EmailNewVisitJob implements ShouldQueue
{
    use Queueable;

    public $data;
    public $toEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $toEmail)
    {
        $this->data = $data;
        $this->toEmail = $toEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->toEmail)->send(new EmailNewVisit([
                'client_reg_id' => $this->data['client_reg_id'],
                'client_name' => $this->data['client_name'],
                'client_service' => $this->data['client_service'],
                'client_service_price' => $this->data['client_service_price'],
                'client_service_commision' => $this->data['client_service_commision'],
                'client_service_is_cupping' => $this->data['client_service_is_cupping'],
                'client_service_started_at' => $this->data['client_service_started_at'],
                'client_service_finished_at' => $this->data['client_service_finished_at'],
                'client_service_status' => $this->data['client_service_status'],
                'client_therapist' => $this->data['client_therapist'],
                'client_created_at' => $this->data['client_created_at'],
            ], 'RSH New Visit ' . $this->data['client_reg_id']));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            throw $th;
        }
    }
}
