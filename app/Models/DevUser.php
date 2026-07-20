<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class DevUser extends Authenticatable
{
    protected $connection = 'mysql';

    protected $table = 'dev_users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
