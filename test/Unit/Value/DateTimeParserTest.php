<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Value;

use DateTimeImmutable;
use DateTimeZone;
use Horde\Icalendar\Value\DateTimeParser;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeParser::class)]
class DateTimeParserTest extends TestCase
{
    public function testParseDate(): void
    {
        $dt = DateTimeParser::parse('20260520');
        $this->assertSame('2026-05-20', $dt->format('Y-m-d'));
        $this->assertSame('00:00:00', $dt->format('H:i:s'));
        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    public function testParseDateTimeUtc(): void
    {
        $dt = DateTimeParser::parse('20260520T143000Z');
        $this->assertSame('2026-05-20', $dt->format('Y-m-d'));
        $this->assertSame('14:30:00', $dt->format('H:i:s'));
        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    public function testParseDateTimeWithTzid(): void
    {
        $dt = DateTimeParser::parse('20260520T090000', 'America/New_York');
        $this->assertSame('2026-05-20', $dt->format('Y-m-d'));
        $this->assertSame('09:00:00', $dt->format('H:i:s'));
        $this->assertSame('America/New_York', $dt->getTimezone()->getName());
    }

    public function testParseDateTimeLocalWithoutTzid(): void
    {
        $dt = DateTimeParser::parse('20260520T090000');
        $this->assertSame('09:00:00', $dt->format('H:i:s'));
        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    public function testParseInvalidThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DateTimeParser::parse('not-a-date');
    }

    public function testFormatUtc(): void
    {
        $dt = new DateTimeImmutable('2026-05-20 14:30:00', new DateTimeZone('UTC'));
        $this->assertSame('20260520T143000Z', DateTimeParser::format($dt));
    }

    public function testFormatWithTzid(): void
    {
        $dt = new DateTimeImmutable('2026-05-20 14:30:00', new DateTimeZone('UTC'));
        $result = DateTimeParser::format($dt, 'America/New_York');
        $this->assertSame('20260520T103000', $result);
    }

    public function testFormatDateOnly(): void
    {
        $dt = new DateTimeImmutable('2026-05-20 14:30:00', new DateTimeZone('UTC'));
        $this->assertSame('20260520', DateTimeParser::format($dt, dateOnly: true));
    }

    public function testFormatLocalTime(): void
    {
        $dt = new DateTimeImmutable('2026-05-20 09:00:00', new DateTimeZone('America/New_York'));
        $result = DateTimeParser::format($dt, 'America/New_York');
        $this->assertSame('20260520T090000', $result);
    }

    public function testIsDate(): void
    {
        $this->assertTrue(DateTimeParser::isDate('20260520'));
        $this->assertFalse(DateTimeParser::isDate('20260520T090000'));
        $this->assertFalse(DateTimeParser::isDate('20260520T090000Z'));
        $this->assertFalse(DateTimeParser::isDate('2026-05-20'));
    }

    public function testRoundTripUtc(): void
    {
        $original = '20260520T143000Z';
        $dt = DateTimeParser::parse($original);
        $this->assertSame($original, DateTimeParser::format($dt));
    }

    public function testRoundTripWithTzid(): void
    {
        $original = '20260520T090000';
        $tzid = 'America/New_York';
        $dt = DateTimeParser::parse($original, $tzid);
        $this->assertSame($original, DateTimeParser::format($dt, $tzid));
    }

    public function testRoundTripDate(): void
    {
        $original = '20260520';
        $dt = DateTimeParser::parse($original);
        $this->assertSame($original, DateTimeParser::format($dt, dateOnly: true));
    }
}
