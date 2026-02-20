<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends BaseModel<Factory>
 */
final class Restaurant extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'address',
        'timezone',
        'currency',
        'is_active',
    ];

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * @return HasMany<DeliveryAddress, $this>
     */
    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
