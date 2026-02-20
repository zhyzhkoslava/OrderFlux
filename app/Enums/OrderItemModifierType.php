<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderItemModifierType: string
{
    case ADD = 'add';
    case REMOVE = 'remove';
}
