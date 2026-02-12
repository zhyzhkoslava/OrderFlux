<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
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
        $restaurantId = $this->route('restaurant')?->id;

        return [
            'name'                 => ['sometimes', 'string', 'max:255'],
            'slug'                 => ['sometimes', 'string', 'max:255', 'unique:restaurants,slug,'.$restaurantId],
            'timezone'             => ['sometimes', 'string', 'max:255'],
            'currency'             => ['sometimes', 'string', 'size:3'],
            'is_active'            => ['sometimes', 'boolean'],
            'address.line1'        => ['sometimes', 'string', 'max:255'],
            'address.line2'        => ['nullable', 'string', 'max:255'],
            'address.city'         => ['sometimes', 'string', 'max:255'],
            'address.region'       => ['nullable', 'string', 'max:255'],
            'address.postal_code'  => ['nullable', 'string', 'max:32'],
            'address.country_code' => ['sometimes', 'string', 'size:2'],
        ];
    }
}
