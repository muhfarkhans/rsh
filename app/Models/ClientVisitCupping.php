<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientVisitCupping extends Model
{
    protected $table = 'client_visit_cuppings';

    protected $fillable = [
        'client_visit_id',
        'therapy_id',
        'cupping_type',
        'temperature',
        'blood_pressure',
        'pulse',
        'respiratory',
        'side_effect',
        'first_action',
        'education_after',
        'subjective',
        'objective',
        'analysis',
        'planning',
        'points',
    ];

    protected $casts = [
        'points' => 'array',
    ];

    public function clientVisit(): BelongsTo
    {
        return $this->belongsTo(ClientVisit::class, 'client_visit_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'therapy_id');
    }
}
