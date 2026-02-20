<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'NEW';
    case ACCEPTED = 'ACCEPTED';
    case PREPARING = 'PREPARING';
    case READY = 'READY';
    case ON_THE_WAY = 'ON_THE_WAY';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
}
