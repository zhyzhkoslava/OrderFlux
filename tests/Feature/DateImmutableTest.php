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
    public function dateNowReturnsCarbonImmutable(): void
    {
        $now = Date::now();

        self::assertInstanceOf(CarbonImmutable::class, $now);
    }

    #[Test]
    public function immutableDateDoesNotMutateOriginal(): void
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
