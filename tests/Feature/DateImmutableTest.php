<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class DateImmutableTest extends TestCase
{
    /** @test */
    public function date_now_returns_carbon_immutable()
    {
        $now = Date::now();

        $this->assertInstanceOf(CarbonImmutable::class, $now);
    }

    /** @test */
    public function immutable_date_does_not_mutate_original()
    {

        $date = Date::now();
        $newDate = $date->addDay();

        $this->assertNotSame($date, $newDate);
        $this->assertEquals($date->format('Y-m-d H:i:s'), Date::now()->subDay()->addDay()->format('Y-m-d H:i:s'));
    }
}
