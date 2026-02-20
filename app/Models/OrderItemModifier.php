<?php

declare(strict_types=1);

namespace App\Models;

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
        'snapshot_name',
        'type',
        'quantity',
        'price_cents',
        'line_total_cents',
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
            'quantity'         => 'integer',
            'price_cents'      => 'integer',
            'line_total_cents' => 'integer',
            'currency'         => 'string',
        ];
    }
}
