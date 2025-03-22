<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientVisitCupping extends Model
{
    protected $table = 'client_visit_cuppings';

    protected $fillable = [
        'client_visit_id',
        'therapy_id',
        'cupping_type',
        'side_effect',
        'first_action',
        'education_after',
        'subjective',
        'objective',
        'analysis',
        'planning',
        'points',
        'service_id',
    ];

    protected $casts = [
        'points' => 'array',
    ];

    public function clientVisit(): HasOne
    {
        return $this->hasOne(ClientVisit::class, 'id', 'client_visit_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapy_id');
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }
}
