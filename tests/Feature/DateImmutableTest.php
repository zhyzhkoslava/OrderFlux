<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DateImmutableTest extends TestCase
{
    #[Test]
    public function date_now_returns_carbon_immutable(): void
    {
        $now = Date::now();

        self::assertInstanceOf(CarbonImmutable::class, $now);
    }

    #[Test]
    public function immutable_date_does_not_mutate_original(): void
    {
        $date = Date::now();
        $newDate = $date->addDay();

        self::assertNotSame($date, $newDate);

        self::assertSame(
            $date->format('Y-m-d H:i:s'),
            $newDate->subDay()->format('Y-m-d H:i:s')
        );
    }
}
