<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderItemModifierType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @extends BaseModel<Factory>
 */
final class OrderItemModifier extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'order_item_id',
        'type',
        'quantity',
        'price_cents',
        'currency',
    ];

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'type'        => OrderItemModifierType::class,
            'quantity'    => 'integer',
            'price_cents' => 'integer',
            'currency'    => Currency::class,
        ];
    }
}
