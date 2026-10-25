<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const METHODS = ['qris_static', 'legacy'];

    public const STATUSES = ['pending', 'paid', 'rejected', 'expired', 'refunded'];

    protected $fillable = [
        'order_id',
        'method',
        'reference',
        'lookup_token_hash',
        'status',
        'amount',
        'reviewed_by',
        'reviewed_at',
        'verification_reference',
        'rejection_reason',
        'expires_at',
    ];

    protected $hidden = ['lookup_token_hash'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
