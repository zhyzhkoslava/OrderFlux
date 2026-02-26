<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpsertOrderRequest extends FormRequest
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
            'order'                                => ['required', 'array'],
            'order.public_id'                      => ['required', 'string', 'max:255'],
            'order.restaurant_public_id'           => ['required', 'string', 'exists:restaurants,public_id'],
            'order.request_delivery_time'          => ['required', 'date'],
            'order.type'                           => ['required', 'in:ASAP,PREORDER'],
            'order.status'                         => ['sometimes', 'in:NEW,ACCEPTED,PREPARING,READY,ON_THE_WAY,COMPLETED,CANCELLED'],
            'customer'                             => ['required', 'array'],
            'customer.public_id'                   => ['required', 'string', 'max:255'],
            'delivery_address'                     => ['required', 'array'],
            'delivery_address.public_id'           => ['nullable', 'string', 'max:255'],
            'delivery_address.city'                => [Rule::requiredIf(fn (): bool => blank($this->input('delivery_address.public_id'))), 'string', 'max:255'],
            'delivery_address.street'              => [Rule::requiredIf(fn (): bool => blank($this->input('delivery_address.public_id'))), 'string', 'max:255'],
            'delivery_address.building'            => [Rule::requiredIf(fn (): bool => blank($this->input('delivery_address.public_id'))), 'string', 'max:255'],
            'delivery_address.apartment'           => ['nullable', 'string', 'max:255'],
            'delivery_address.comment'             => ['nullable', 'string', 'max:1024'],
            'order_item'                           => ['required', 'array', 'min:1'],
            'order_item.*.public_id'               => ['required', 'string', 'max:255'],
            'order_item.*.quantity'                => ['required', 'integer', 'min:1'],
            'order_item.*.modifiers'               => ['sometimes', 'array'],
            'order_item.*.modifiers.*.public_id'   => ['required_with:order_item.*.modifiers', 'string', 'max:255'],
            'order_item.*.modifiers.*.type'        => ['required_with:order_item.*.modifiers', 'in:add,remove'],
            'order_item.*.modifiers.*.quantity'    => ['required_with:order_item.*.modifiers', 'integer', 'min:1'],
        ];
    }
}
