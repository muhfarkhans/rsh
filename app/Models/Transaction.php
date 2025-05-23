<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'total_discount',
        'payment_method',
        'status',
        'photo',
    ];

    public function clientVisit()
    {
        return $this->hasOne(ClientVisit::class, 'id', 'client_visit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function discount()
    {
        return $this->belongsTo(TransactionDiscount::class, 'id', 'transaction_id');
    }

    public function discountName()
    {
        return $this->belongsTo(TransactionDiscount::class, 'id', 'transaction_id');
    }

    public function discountPeriod()
    {
        return $this->belongsTo(TransactionDiscount::class, 'id', 'transaction_id');
    }

    public function service()
    {
        return $this->belongsTo(TransactionItem::class, 'id', 'transaction_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function itemServiceName()
    {
        return $this->items();
    }

    public function itemServicePrice()
    {
        return $this->items();
    }

    public function itemServiceAddName()
    {
        return $this->items();
    }

    public function itemServiceAddPrice()
    {
        return $this->items();
    }
}