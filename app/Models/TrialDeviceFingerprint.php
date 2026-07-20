<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialDeviceFingerprint extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'fingerprint',
        'tenant_id',
        'ip_address',
        'user_agent',
        'received_trial',
    ];

    protected $casts = [
        'received_trial' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
