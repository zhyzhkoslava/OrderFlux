<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @extends BaseModel<Factory>
 */
final class Order extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'restaurant_id',
        'customer_id',
        'delivery_address_id',
        'scheduled_at',
        'type',
        'status',
        'total_cents',
        'delivery_fee_cents',
        'currency',
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
     * @return BelongsTo<DeliveryAddress, $this>
     */
    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(DeliveryAddress::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'scheduled_at'       => 'immutable_datetime',
            'type'               => OrderType::class,
            'status'             => OrderStatus::class,
            'total_cents'        => 'integer',
            'delivery_fee_cents' => 'integer',
            'currency'           => Currency::class,
        ];
    }
}
