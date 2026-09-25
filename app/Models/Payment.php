<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAID = 'paid';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transaction_id',
        'order_id',
        'snap_token',
        'status',
        'method',
        'midtrans_transaction_id',
        'amount',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // Relationships

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // State

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Mark the payment as paid. Only transitions out of "active" are applied.
     */
    public function markPaid(?string $method, ?string $gatewayId, mixed $paidAt = null): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PAID,
            'method' => $method,
            'midtrans_transaction_id' => $gatewayId,
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    /**
     * Mark the payment as expired. Only transitions out of "active" are applied.
     */
    public function markExpired(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Retire the payment session (e.g. replaced by Reset). Only transitions
     * out of "active" are applied.
     */
    public function markCancelled(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
