<?php

namespace App\Jobs;

use App\Mail\EmailNewTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Mail;

class EmailNewTransactionJob implements ShouldQueue
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
            Mail::to($this->toEmail)->send(new EmailNewTransaction([
                'client_reg_id' => $this->data['client_reg_id'],
                'client_name' => $this->data['client_name'],
                'client_service' => $this->data['client_service'],
                'client_service_price' => $this->data['client_service_price'],
                'client_service_commision' => $this->data['client_service_commision'],
                'client_service_is_cupping' => $this->data['client_service_is_cupping'],
                'client_service_started_at' => $this->data['client_service_started_at'],
                'client_service_finished_at' => $this->data['client_service_finished_at'],
                'client_service_status' => $this->data['client_service_status'],
                'client_transaction_created_by' => $this->data['client_transaction_created_by'],
                'client_transaction_invoice' => $this->data['client_transaction_invoice'],
                'client_transaction_additional' => $this->data['client_transaction_additional'],
                'client_transaction_discount' => $this->data['client_transaction_discount'],
                'client_transaction_amount' => $this->data['client_transaction_amount'],
                'client_transaction_payment_method' => $this->data['client_transaction_payment_method'],
                'client_transaction_status' => $this->data['client_transaction_status'],
                'client_therapist' => $this->data['client_therapist'],
                'client_created_at' => $this->data['client_created_at'],
            ], 'RSH New Transaction ' . $this->data['client_reg_id']));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            throw $th;
        }
    }
}
