<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Calendar;

use Horde\Icalendar\Calendar\Valarm;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Component\AbstractComponent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vevent::class)]
class VeventTest extends TestCase
{
    public function testType(): void
    {
        $event = new Vevent();
        $this->assertSame('VEVENT', $event->getType());
    }

    public function testGetAlarms(): void
    {
        $event = new Vevent();
        $alarm1 = new Valarm();
        $alarm2 = new Valarm();

        $event->addChild($alarm1);
        $event->addChild($alarm2);

        $alarms = $event->getAlarms();
        $this->assertCount(2, $alarms);
        $this->assertSame($alarm1, $alarms[0]);
        $this->assertSame($alarm2, $alarms[1]);
    }

    public function testGetAlarmsEmpty(): void
    {
        $event = new Vevent();
        $this->assertSame([], $event->getAlarms());
    }

    public function testParseWithAlarm(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:event-alarm@example.com',
            'SUMMARY:Meeting with alarm',
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'TRIGGER:-PT15M',
            'DESCRIPTION:Reminder',
            'END:VALARM',
            'END:VEVENT',
            '',
        ]);

        $event = AbstractComponent::fromString($input);
        $this->assertInstanceOf(Vevent::class, $event);

        assert($event instanceof Vevent);
        $alarms = $event->getAlarms();
        $this->assertCount(1, $alarms);
        $this->assertInstanceOf(Valarm::class, $alarms[0]);
        $this->assertSame('DISPLAY', $alarms[0]->getProperties()->getValue('ACTION'));
        $this->assertSame('-PT15M', $alarms[0]->getProperties()->getValue('TRIGGER'));
    }

    public function testRoundTrip(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'SUMMARY:Test Event',
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'TRIGGER:-PT10M',
            'END:VALARM',
            'END:VEVENT',
            '',
        ]);

        $event = AbstractComponent::fromString($input);
        $this->assertSame($input, $event->toString());
    }
}
