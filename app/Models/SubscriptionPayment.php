<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $connection = 'mysql';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'plan',
        'amount',
        'currency',
        'reference',
        'status',
        'paystack_status',
        'channel',
        'paid_email',
        'paid_at',
        'period_starts_at',
        'period_ends_at',
        'payload',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'period_starts_at' => 'datetime',
        'period_ends_at' => 'datetime',
        'payload' => 'array',
        'amount' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
