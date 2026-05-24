<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, CreatedUpdatedBy, SoftDeletes;

    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'amount',
        'currency',
        'transaction_id',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'payment_data',
        'metadata',
        'failure_reason',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'payment_method' => PaymentMethodEnum::class,
        'status' => PaymentStatusEnum::class,
        'amount' => 'decimal:3',
        'payment_data' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the order that owns the payment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mark payment as succeeded
     */
    public function markAsSucceeded(?string $transactionId = null): bool
    {
        $this->status = PaymentStatusEnum::SUCCEEDED;
        $this->paid_at = now();
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        return $this->save();
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(?string $reason = null): bool
    {
        $this->status = PaymentStatusEnum::FAILED;
        $this->failed_at = now();
        if ($reason) {
            $this->failure_reason = $reason;
        }
        return $this->save();
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatusEnum::SUCCEEDED;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatusEnum::FAILED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    /**
     * Scope to get successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', PaymentStatusEnum::SUCCEEDED);
    }

    /**
     * Scope to get failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatusEnum::FAILED);
    }

    /**
     * Scope to get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatusEnum::PENDING);
    }
}
