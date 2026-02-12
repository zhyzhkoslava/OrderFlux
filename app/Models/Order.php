<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Order extends BaseModel
{
    use HasFactory;
    use HasPublicId;

    public const TYPE_ASAP = 'ASAP';

    public const TYPE_PREORDER = 'PREORDER';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_CONFIRMED = 'CONFIRMED';

    public const STATUS_PREPARING = 'PREPARING';

    public const STATUS_DELIVERING = 'DELIVERING';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

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
        'order_amount',
        'delivery_amount',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'restaurant_id',
        'customer_id',
        'delivery_address_id',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(DeliveryAddress::class);
    }

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
            'scheduled_at' => 'immutable_datetime',
            'created_at'   => 'immutable_datetime',
            'updated_at'   => 'immutable_datetime',
        ];
    }
}
