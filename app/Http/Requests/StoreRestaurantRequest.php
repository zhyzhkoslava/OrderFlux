<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
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
            'name'                 => ['required', 'string', 'max:255'],
            'slug'                 => ['required', 'string', 'max:255', 'unique:restaurants,slug'],
            'timezone'             => ['required', 'string', 'max:255'],
            'currency'             => ['required', 'string', 'size:3'],
            'is_active'            => ['sometimes', 'boolean'],
            'address.line1'        => ['required', 'string', 'max:255'],
            'address.line2'        => ['nullable', 'string', 'max:255'],
            'address.city'         => ['required', 'string', 'max:255'],
            'address.region'       => ['nullable', 'string', 'max:255'],
            'address.postal_code'  => ['nullable', 'string', 'max:32'],
            'address.country_code' => ['required', 'string', 'size:2'],
        ];
    }
}
