<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presence extends Model
{
    use SoftDeletes;

    protected $table = 'presences';

    protected $fillable = [
        'user_id',
        'status',
        'check_in',
        'check_out',
        'notes',
    ];
}
