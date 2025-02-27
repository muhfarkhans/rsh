<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientVisit extends Model
{
    use SoftDeletes;

    protected $table = 'client_visits';

    protected $fillable = [
        'client_id',
        'created_by',
        'therapy_id',
        'complaint',
        'medical_history',
        'family_medical_history',
        'medication_history',
        'sleep_habits',
        'exercise',
        'nutrition',
        'spiritual',
        'diagnose',
        'status',
        'started_at',
        'ended_at',
        'relation_as',
        'signature_therapist',
        'signature_client',
    ];

    protected $casts = [
        'medical_history' => 'array',
        'family_medical_history' => 'array',
        'medication_history' => 'array',
        'sleep_habits' => 'array',
        'exercise' => 'array',
        'nutrition' => 'array',
        'spiritual' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function therapy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapy_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function clientVisitCheck(): BelongsTo
    {
        return $this->belongsTo(ClientVisitCheck::class, 'id', 'client_visit_id');
    }

    public function clientVisitCupping(): BelongsTo
    {
        return $this->belongsTo(ClientVisitCupping::class, 'id', 'client_visit_id');
    }

    public function clientVisitServices(): HasMany
    {
        return $this->hasMany(ClientVisitService::class, 'client_visit_id', 'id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'id', 'client_visit_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'client_visit_id', 'id');
    }
}
