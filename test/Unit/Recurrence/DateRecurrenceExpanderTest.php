<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Recurrence;

use DateTimeImmutable;
use Horde\Icalendar\Recurrence\DateRecurrenceExpander;
use Horde\Icalendar\Recurrence\RecurrenceInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateRecurrenceExpander::class)]
#[RequiresMethod(\Horde\Date\Recurrence\Recurrence::class, '__construct')]
class DateRecurrenceExpanderTest extends TestCase
{
    private DateRecurrenceExpander $expander;

    protected function setUp(): void
    {
        $this->expander = new DateRecurrenceExpander();
    }

    public function testDailyRecurrence(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: 'FREQ=DAILY;INTERVAL=1;COUNT=5',
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-25T23:59:59Z'),
        );

        $this->assertCount(5, $set);
        $this->assertEquals(new DateTimeImmutable('2026-05-20T09:00:00Z'), $set->first());
        $this->assertEquals(new DateTimeImmutable('2026-05-24T09:00:00Z'), $set->last());
    }

    public function testWeeklyRecurrence(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-18T10:00:00Z'),
            rrule: 'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,WE,FR',
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-18T00:00:00Z'),
            new DateTimeImmutable('2026-05-24T23:59:59Z'),
        );

        $this->assertCount(3, $set);
    }

    public function testExdateExclusion(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: 'FREQ=DAILY;INTERVAL=1;COUNT=5',
            exdates: [
                new DateTimeImmutable('2026-05-21T09:00:00Z'),
                new DateTimeImmutable('2026-05-23T09:00:00Z'),
            ],
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-25T23:59:59Z'),
        );

        $this->assertCount(3, $set);
        $this->assertFalse($set->contains(new DateTimeImmutable('2026-05-21T09:00:00Z')));
        $this->assertFalse($set->contains(new DateTimeImmutable('2026-05-23T09:00:00Z')));
    }

    public function testRdateInclusion(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: 'FREQ=DAILY;INTERVAL=1;COUNT=3',
            rdates: [
                new DateTimeImmutable('2026-05-30T09:00:00Z'),
            ],
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-31T23:59:59Z'),
        );

        $this->assertCount(4, $set);
        $this->assertTrue($set->contains(new DateTimeImmutable('2026-05-30T09:00:00Z')));
    }

    public function testRdateExcludedByExdate(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: null,
            exdates: [new DateTimeImmutable('2026-05-25T09:00:00Z')],
            rdates: [new DateTimeImmutable('2026-05-25T09:00:00Z')],
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-31T23:59:59Z'),
        );

        $this->assertFalse($set->contains(new DateTimeImmutable('2026-05-25T09:00:00Z')));
    }

    public function testNoRruleSingleOccurrence(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-20T23:59:59Z'),
        );

        $this->assertCount(1, $set);
        $this->assertEquals(new DateTimeImmutable('2026-05-20T09:00:00Z'), $set->first());
    }

    public function testNoRruleWithRdates(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rdates: [
                new DateTimeImmutable('2026-05-25T09:00:00Z'),
                new DateTimeImmutable('2026-05-30T09:00:00Z'),
            ],
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-20T00:00:00Z'),
            new DateTimeImmutable('2026-05-31T23:59:59Z'),
        );

        $this->assertCount(3, $set);
    }

    public function testEmptyRangeReturnsEmpty(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: 'FREQ=DAILY;INTERVAL=1;COUNT=5',
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-06-01T00:00:00Z'),
            new DateTimeImmutable('2026-06-30T23:59:59Z'),
        );

        $this->assertTrue($set->isEmpty());
    }

    public function testDtstartOutsideRange(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: 'FREQ=DAILY;INTERVAL=1',
        );

        $set = $this->expander->expand(
            $input,
            new DateTimeImmutable('2026-05-22T00:00:00Z'),
            new DateTimeImmutable('2026-05-24T23:59:59Z'),
        );

        $this->assertCount(3, $set);
        $this->assertEquals(new DateTimeImmutable('2026-05-22T09:00:00Z'), $set->first());
    }
}
