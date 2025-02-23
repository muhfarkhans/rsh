<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'reg_id',
        'name',
        'phone',
        'birthdate',
        'gender',
        'job',
        'address',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(ClientVisit::class, 'client_id', 'id');
    }
}
