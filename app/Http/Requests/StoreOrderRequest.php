<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'public_id'            => ['nullable', 'uuid', 'unique:orders,public_id'],
            'restaurant_public_id' => ['required', 'uuid', 'exists:restaurants,public_id'],
            'customer_public_id'   => ['required', 'uuid', 'exists:customers,public_id'],
            'type'                 => ['required', Rule::in([Order::TYPE_ASAP, Order::TYPE_PREORDER])],
            'status'               => ['sometimes', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PREPARING,
                Order::STATUS_DELIVERING,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ])],
            'scheduled_at'                   => ['nullable', 'date', 'required_if:type,'.Order::TYPE_PREORDER],
            'delivery_amount'                => ['required', 'integer', 'min:0'],
            'delivery_address.line1'         => ['required', 'string', 'max:255'],
            'delivery_address.line2'         => ['nullable', 'string', 'max:255'],
            'delivery_address.city'          => ['required', 'string', 'max:255'],
            'delivery_address.region'        => ['nullable', 'string', 'max:255'],
            'delivery_address.postal_code'   => ['nullable', 'string', 'max:32'],
            'delivery_address.country_code'  => ['required', 'string', 'size:2'],
            'delivery_address.instructions'  => ['nullable', 'string', 'max:1000'],
            'items'                          => ['required', 'array', 'min:1'],
            'items.*.name'                   => ['required', 'string', 'max:255'],
            'items.*.sku'                    => ['nullable', 'string', 'max:255'],
            'items.*.quantity'               => ['required', 'integer', 'min:1'],
            'items.*.unit_price'             => ['required', 'integer', 'min:0'],
            'items.*.modifiers'              => ['nullable', 'array'],
            'items.*.modifiers.*.name'       => ['required', 'string', 'max:255'],
            'items.*.modifiers.*.quantity'   => ['sometimes', 'integer', 'min:1'],
            'items.*.modifiers.*.unit_price' => ['required', 'integer', 'min:0'],
        ];
    }
}
