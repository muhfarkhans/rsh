<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientVisitService extends Model
{
    use SoftDeletes;

    protected $table = 'client_visit_services';

    protected $fillable = [
        'client_visit_id',
        'service_id',
        'user_id',
        'start_time',
        'end_time',
    ];

    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class, 'client_visit_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
