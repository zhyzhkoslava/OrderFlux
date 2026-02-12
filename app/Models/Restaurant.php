<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends BaseModel
{
    use HasFactory;
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'address_line1',
        'address_line2',
        'address_city',
        'address_region',
        'address_postal_code',
        'address_country_code',
        'timezone',
        'currency',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'id',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
