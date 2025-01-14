<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'client_visit_id',
        'created_by',
        'invoice_id',
        'amount',
        'payment_method',
        'status',
    ];

    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class, 'client_visit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
