<?php

namespace App\Models;

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
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the validation rules for order data
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000',
        ];
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

    /**
     * Complete the order
     */
    public function complete(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if order is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope to get pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved orders
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected orders
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
