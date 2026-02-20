<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @extends BaseModel<Factory>
 */
final class OrderItem extends BaseModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'order_id',
        'sku',
        'snapshot_name',
        'qty',
        'price_cents',
        'line_total_cents',
        'currency',
    ];

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany<OrderItemModifier, $this>
     */
    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'qty'              => 'integer',
            'price_cents'      => 'integer',
            'line_total_cents' => 'integer',
            'currency'         => 'string',
        ];
    }
}
