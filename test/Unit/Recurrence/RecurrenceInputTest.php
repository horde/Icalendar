<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Recurrence;

use DateTimeImmutable;
use Horde\Icalendar\Recurrence\RecurrenceInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecurrenceInput::class)]
class RecurrenceInputTest extends TestCase
{
    public function testMinimalConstruction(): void
    {
        $dtstart = new DateTimeImmutable('2026-05-20T09:00:00Z');
        $input = new RecurrenceInput($dtstart);

        $this->assertSame($dtstart, $input->dtstart);
        $this->assertNull($input->rrule);
        $this->assertSame([], $input->exdates);
        $this->assertSame([], $input->rdates);
    }

    public function testFullConstruction(): void
    {
        $dtstart = new DateTimeImmutable('2026-01-05T10:00:00Z');
        $exdate1 = new DateTimeImmutable('2026-01-12T10:00:00Z');
        $exdate2 = new DateTimeImmutable('2026-01-19T10:00:00Z');
        $rdate1 = new DateTimeImmutable('2026-02-14T10:00:00Z');

        $input = new RecurrenceInput(
            dtstart: $dtstart,
            rrule: 'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO',
            exdates: [$exdate1, $exdate2],
            rdates: [$rdate1],
        );

        $this->assertSame($dtstart, $input->dtstart);
        $this->assertSame('FREQ=WEEKLY;INTERVAL=1;BYDAY=MO', $input->rrule);
        $this->assertCount(2, $input->exdates);
        $this->assertSame($exdate1, $input->exdates[0]);
        $this->assertSame($exdate2, $input->exdates[1]);
        $this->assertCount(1, $input->rdates);
        $this->assertSame($rdate1, $input->rdates[0]);
    }

    public function testWithEmptyRrule(): void
    {
        $input = new RecurrenceInput(
            dtstart: new DateTimeImmutable('2026-05-20T09:00:00Z'),
            rrule: null,
        );

        $this->assertNull($input->rrule);
    }
}
