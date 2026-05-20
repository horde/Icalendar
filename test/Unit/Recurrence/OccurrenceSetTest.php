<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Recurrence;

use DateTimeImmutable;
use Horde\Icalendar\Recurrence\OccurrenceSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OccurrenceSet::class)]
class OccurrenceSetTest extends TestCase
{
    public function testEmptySet(): void
    {
        $set = new OccurrenceSet([]);

        $this->assertSame(0, $set->count());
        $this->assertTrue($set->isEmpty());
        $this->assertNull($set->first());
        $this->assertNull($set->last());
        $this->assertSame([], $set->toArray());
    }

    public function testSingleDate(): void
    {
        $date = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $set = new OccurrenceSet([$date]);

        $this->assertSame(1, $set->count());
        $this->assertFalse($set->isEmpty());
        $this->assertEquals($date, $set->first());
        $this->assertEquals($date, $set->last());
    }

    public function testMultipleDates(): void
    {
        $d1 = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $d2 = new DateTimeImmutable('2026-05-21T09:00:00Z');
        $d3 = new DateTimeImmutable('2026-05-22T09:00:00Z');

        $set = new OccurrenceSet([$d1, $d2, $d3]);

        $this->assertSame(3, $set->count());
        $this->assertEquals($d1, $set->first());
        $this->assertEquals($d3, $set->last());
    }

    public function testSortsInputDates(): void
    {
        $d1 = new DateTimeImmutable('2026-05-22T09:00:00Z');
        $d2 = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $d3 = new DateTimeImmutable('2026-05-21T09:00:00Z');

        $set = new OccurrenceSet([$d1, $d2, $d3]);

        $this->assertEquals($d2, $set->first());
        $this->assertEquals($d1, $set->last());

        $array = $set->toArray();
        $this->assertEquals($d2, $array[0]);
        $this->assertEquals($d3, $array[1]);
        $this->assertEquals($d1, $array[2]);
    }

    public function testContains(): void
    {
        $d1 = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $d2 = new DateTimeImmutable('2026-05-21T09:00:00Z');

        $set = new OccurrenceSet([$d1, $d2]);

        $this->assertTrue($set->contains(new DateTimeImmutable('2026-05-20T09:00:00Z')));
        $this->assertTrue($set->contains(new DateTimeImmutable('2026-05-21T09:00:00Z')));
        $this->assertFalse($set->contains(new DateTimeImmutable('2026-05-22T09:00:00Z')));
    }

    public function testContainsMatchesByValue(): void
    {
        $d1 = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $set = new OccurrenceSet([$d1]);

        $different = new DateTimeImmutable('2026-05-20T09:00:00+00:00');
        $this->assertTrue($set->contains($different));
    }

    public function testIteration(): void
    {
        $d1 = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $d2 = new DateTimeImmutable('2026-05-21T09:00:00Z');

        $set = new OccurrenceSet([$d1, $d2]);

        $collected = [];
        foreach ($set as $date) {
            $collected[] = $date;
        }

        $this->assertCount(2, $collected);
        $this->assertEquals($d1, $collected[0]);
        $this->assertEquals($d2, $collected[1]);
    }

    public function testCountable(): void
    {
        $dates = [
            new DateTimeImmutable('2026-05-20T09:00:00Z'),
            new DateTimeImmutable('2026-05-21T09:00:00Z'),
            new DateTimeImmutable('2026-05-22T09:00:00Z'),
        ];

        $set = new OccurrenceSet($dates);

        $this->assertCount(3, $set);
    }
}
