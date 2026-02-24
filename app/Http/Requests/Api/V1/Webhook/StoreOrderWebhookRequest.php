<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Webhook;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order'                            => ['required', 'array'],
            'order.public_id'                  => ['required', 'string', 'max:255'],
            'order.restaurant_public_id'       => ['required', 'string', 'exists:restaurants,public_id'],
            'order.requested_at'               => ['required', 'date'],
            'order.type'                       => ['required', 'in:ASAP,PREORDER'],
            'order.total_cents'                => ['required', 'integer', 'min:0'],
            'order.delivery_fee_cents'         => ['required', 'integer', 'min:0'],
            'order.currency'                   => ['required', 'in:USD,EUR,UAH'],
            'order.status'                     => ['sometimes', 'in:NEW,ACCEPTED,PREPARING,READY,ON_THE_WAY,COMPLETED,CANCELLED'],
            'customer'                         => ['required', 'array'],
            'customer.public_id'               => ['required', 'string', 'max:255'],
            'customer.name'                    => ['required', 'string', 'max:255'],
            'customer.phone'                   => ['required', 'string', 'max:255'],
            'customer.email'                   => ['nullable', 'email', 'max:255'],
            'delivery_address'                 => ['required', 'array'],
            'delivery_address.public_id'       => ['required', 'string', 'max:255'],
            'delivery_address.city'            => ['required', 'string', 'max:255'],
            'delivery_address.street'          => ['required', 'string', 'max:255'],
            'delivery_address.building'        => ['required', 'string', 'max:255'],
            'delivery_address.apartment'       => ['nullable', 'string', 'max:255'],
            'delivery_address.comment'         => ['nullable', 'string', 'max:1024'],
            'items'                            => ['required', 'array', 'min:1'],
            'items.*.public_id'                => ['required', 'string', 'max:255'],
            'items.*.sku'                      => ['required', 'string', 'max:255'],
            'items.*.qty'                      => ['required', 'integer', 'min:1'],
            'items.*.price_cents'              => ['required', 'integer', 'min:0'],
            'items.*.currency'                 => ['required', 'in:USD,EUR,UAH'],
            'items.*.modifiers'                => ['sometimes', 'array'],
            'items.*.modifiers.*.public_id'    => ['required_with:items.*.modifiers', 'string', 'max:255'],
            'items.*.modifiers.*.type'         => ['required_with:items.*.modifiers', 'in:add,remove'],
            'items.*.modifiers.*.quantity'     => ['required_with:items.*.modifiers', 'integer', 'min:1'],
            'items.*.modifiers.*.price_cents'  => ['required_with:items.*.modifiers', 'integer', 'min:0'],
            'items.*.modifiers.*.currency'     => ['required_with:items.*.modifiers', 'in:USD,EUR,UAH'],
        ];
    }
}
