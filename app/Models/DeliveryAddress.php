<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends BaseModel<Factory>
 */
final class DeliveryAddress extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'restaurant_id',
        'customer_id',
        'city',
        'street',
        'building',
        'apartment',
        'comment',
    ];

    /**
     * @return BelongsTo<Restaurant, $this>
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
