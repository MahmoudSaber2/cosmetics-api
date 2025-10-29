<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
    ];

    /**
     * Get the validation rules for client data
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get the orders for the client
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the client's full contact information
     */
    public function getFullContactAttribute(): string
    {
        $contact = $this->name;
        if ($this->email) {
            $contact .= ' (' . $this->email . ')';
        }
        if ($this->phone) {
            $contact .= ' - ' . $this->phone;
        }
        return $contact;
    }

    /**
     * Get the client's full address
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->address ?? '';
        if ($this->city) {
            $address .= ($address ? ', ' : '') . $this->city;
        }
        return $address;
    }
}
