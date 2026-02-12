<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends BaseModel
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_item_id',
        'name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'order_item_id',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
