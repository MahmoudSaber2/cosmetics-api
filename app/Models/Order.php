<?php

namespace App\Models;

use App\Enums\OrderPaidEnum;
use App\Enums\OrderStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, CreatedUpdatedBy, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'total_amount',
        'total_cost',
        'status',
        'payment_status',
        'note',
        'discount',
        'discount_type',
        'total_after_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OrderStatusEnum::class,
        'payment_status' => OrderPaidEnum::class,
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $date = now()->format('dmY');      // e.g. 11202025
            $random = rand(1000, 9999);        // 4 random digits

            $model->number = 'ORD-' . $date . $random;
        });
    }

    /**
     * Get the client that owns the order
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the order items for the order
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for the order
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment for the order
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    /**
     * Calculate total amount from order items
     */
    public function calculateTotal(): float
    {
        return $this->orderItems->sum('subtotal');
    }

    /**
     * Update the total amount based on order items
     */
    public function updateTotal(): bool
    {
        $this->total_amount = $this->calculateTotal();
        return $this->save();
    }

    /**
     * Approve the order
     */
    public function approve(): bool
    {
        $this->status = OrderStatusEnum::APPROVED;
        return $this->save();
    }

    /**
     * Reject the order
     */
    public function reject(?string $reason = null): bool
    {
        $this->status = OrderStatusEnum::REJECTED;
        if ($reason) {
            $this->rejection_reason = $reason;
        }
        return $this->save();
    }

    private function cleanNumber($value)
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    public function getTotalAmountAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getTotalCostAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getTotalAfterDiscountAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getDiscountAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(): bool
    {
        $this->payment_status = OrderPaidEnum::PAID;
        $this->status = OrderStatusEnum::APPROVED;
        return $this->save();
    }

    /**
     * Mark order as unpaid
     */
    public function markAsUnpaid(): bool
    {
        $this->payment_status = OrderPaidEnum::UNPAID;
        return $this->save();
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === OrderPaidEnum::PAID;
    }

    /**
     * Check if order is unpaid
     */
    public function isUnpaid(): bool
    {
        return $this->payment_status === OrderPaidEnum::UNPAID;
    }

    /**
     * Get the amount to be paid (after discount)
     */
    public function getPayableAmount(): float
    {
        return $this->total_after_discount > 0 ? $this->total_after_discount : $this->total_amount;
    }

}
