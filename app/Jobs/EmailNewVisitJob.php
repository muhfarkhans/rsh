<?php

namespace App\Jobs;

use App\Mail\EmailNewVisit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Mail;

class EmailNewVisitJob implements ShouldQueue
{
    use Queueable;

    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $toEmail = env('MAIL_TO_ADDRESS', 'default-recipient@example.com');

        Mail::to($toEmail)->send(new EmailNewVisit([
            'client_reg_id' => $this->data['client_reg_id'],
            'client_name' => $this->data['client_name'],
            'client_service' => $this->data['client_service'],
            'client_therapist' => $this->data['client_therapist'],
            'client_created_at' => $this->data['client_created_at'],
        ]));
    }
}
