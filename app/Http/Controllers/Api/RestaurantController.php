<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RestaurantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RestaurantResource::collection(Restaurant::query()->latest()->paginate());
    }

    public function store(StoreRestaurantRequest $request): RestaurantResource
    {
        $data = $request->validated();

        $restaurant = Restaurant::query()->create([
            'name'                 => $data['name'],
            'slug'                 => $data['slug'],
            'timezone'             => $data['timezone'],
            'currency'             => mb_strtoupper((string) $data['currency']),
            'is_active'            => $data['is_active'] ?? true,
            'address_line1'        => $data['address']['line1'],
            'address_line2'        => $data['address']['line2'] ?? null,
            'address_city'         => $data['address']['city'],
            'address_region'       => $data['address']['region'] ?? null,
            'address_postal_code'  => $data['address']['postal_code'] ?? null,
            'address_country_code' => mb_strtoupper((string) $data['address']['country_code']),
        ]);

        return new RestaurantResource($restaurant);
    }

    public function show(Restaurant $restaurant): RestaurantResource
    {
        return new RestaurantResource($restaurant);
    }

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): RestaurantResource
    {
        $data = $request->validated();

        $restaurant->fill([
            'name'                 => $data['name'] ?? $restaurant->name,
            'slug'                 => $data['slug'] ?? $restaurant->slug,
            'timezone'             => $data['timezone'] ?? $restaurant->timezone,
            'currency'             => isset($data['currency']) ? mb_strtoupper((string) $data['currency']) : $restaurant->currency,
            'is_active'            => $data['is_active'] ?? $restaurant->is_active,
            'address_line1'        => $data['address']['line1'] ?? $restaurant->address_line1,
            'address_line2'        => $data['address']['line2'] ?? $restaurant->address_line2,
            'address_city'         => $data['address']['city'] ?? $restaurant->address_city,
            'address_region'       => $data['address']['region'] ?? $restaurant->address_region,
            'address_postal_code'  => $data['address']['postal_code'] ?? $restaurant->address_postal_code,
            'address_country_code' => isset($data['address']['country_code'])
                ? mb_strtoupper((string) $data['address']['country_code'])
                : $restaurant->address_country_code,
        ]);

        $restaurant->save();

        return new RestaurantResource($restaurant);
    }

    public function destroy(Restaurant $restaurant): Response
    {
        $restaurant->delete();

        return response()->noContent();
    }
}
