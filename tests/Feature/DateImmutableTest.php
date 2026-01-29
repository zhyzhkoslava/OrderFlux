<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

final class DateImmutableTest extends TestCase
{
    public function testDateNowReturnsCarbonImmutable(): void
    {
        $now = Date::now();

        self::assertInstanceOf(CarbonImmutable::class, $now);
    }

    public function testImmutableDateDoesNotMutateOriginal(): void
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
