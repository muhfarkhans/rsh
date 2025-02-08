<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use SoftDeletes;

    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        'service_id',
        'name',
        'qty',
        'price',
        'is_additional',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
