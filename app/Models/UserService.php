<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserService extends Model
{
    use SoftDeletes;

    protected $table = 'user_services';

    protected $fillable = [
        'service_id',
        'user_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_id', 'id');
    }
}
