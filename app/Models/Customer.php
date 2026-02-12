<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends BaseModel
{
    use HasFactory;
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'phone',
        'name',
        'email',
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
