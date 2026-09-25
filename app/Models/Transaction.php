<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Transaction extends Model
{
    // use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'invoice_number',
        'name',
        'phone',
        'address',
        'total_amount',
        'status',
        'payment_type',
        'paid_at',
        'expires_at',
        'is_complete',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_complete' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Ymd').'-';
        do {
            $candidate = $prefix.strtoupper(Str::random(6));
        } while (self::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activePayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', Payment::STATUS_ACTIVE)->latestOfMany();
    }

    public function paidPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', Payment::STATUS_PAID)->latestOfMany();
    }

    // State

    /**
     * Whether the transaction still accepts payment actions
     * (choose, reset, sync, cancel).
     */
    public function isMutable(): bool
    {
        return $this->status === self::STATUS_PENDING && ! $this->is_complete;
    }

    /**
     * Mark the transaction as paid. Must be called together with the
     * matching payment row transition inside one database transaction.
     */
    public function markPaid(?string $paymentType, mixed $paidAt = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_type' => $paymentType,
            'paid_at' => $paidAt ?? now(),
            'is_complete' => true,
        ]);
    }

    /**
     * Mark the transaction as cancelled. Stock release is handled by the
     * caller (service/command) inside the same database transaction.
     */
    public function markCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'is_complete' => true,
        ]);
    }

    /**
     * Map a Midtrans transaction status to the local status.
     *
     * Returns null when the Midtrans status does not change the lifecycle.
     */
    public static function mapMidtransStatus(string $midtransStatus): ?string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'paid',
            'pending' => 'pending',
            'cancel', 'deny', 'expire' => 'cancelled',
            default => null,
        };
    }
}
