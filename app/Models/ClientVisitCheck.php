<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientVisitCheck extends Model
{
    use SoftDeletes;

    protected $table = 'client_visit_checks';

    protected $fillable = [
        'client_visit_id',
        'temperature',
        'blood_pressure',
        'pulse',
        'respiratory',
        'weight',
        'height',
        'other',
    ];

    protected $casts = [
        'other' => 'array',
    ];

    public function clientVisit(): BelongsTo
    {
        return $this->belongsTo(ClientVisit::class, 'client_visit_id');
    }
}
