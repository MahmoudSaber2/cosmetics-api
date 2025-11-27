<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
        $this->status = 'approved';
        return $this->save();
    }

    /**
     * Reject the order
     */
    public function reject(string $reason = null): bool
    {
        $this->status = 'rejected';
        if ($reason) {
            $this->notes = $reason;
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


}
