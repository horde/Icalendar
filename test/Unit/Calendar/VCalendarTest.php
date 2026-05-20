<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Calendar;

use Horde\Icalendar\Calendar\VCalendar;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Calendar\Vfreebusy;
use Horde\Icalendar\Calendar\Vjournal;
use Horde\Icalendar\Calendar\Vtimezone;
use Horde\Icalendar\Calendar\Vtodo;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\RootComponent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VCalendar::class)]
class VCalendarTest extends TestCase
{
    public function testType(): void
    {
        $cal = new VCalendar();
        $this->assertSame('VCALENDAR', $cal->getType());
    }

    public function testImplementsRootComponent(): void
    {
        $cal = new VCalendar();
        $this->assertInstanceOf(RootComponent::class, $cal);
    }

    public function testGetEvents(): void
    {
        $cal = new VCalendar();
        $event1 = new Vevent();
        $event2 = new Vevent();
        $todo = new Vtodo();

        $cal->addChild($event1);
        $cal->addChild($todo);
        $cal->addChild($event2);

        $events = $cal->getEvents();
        $this->assertCount(2, $events);
        $this->assertSame($event1, $events[0]);
        $this->assertSame($event2, $events[1]);
    }

    public function testGetTodos(): void
    {
        $cal = new VCalendar();
        $cal->addChild(new Vevent());
        $cal->addChild(new Vtodo());
        $cal->addChild(new Vtodo());

        $this->assertCount(2, $cal->getTodos());
    }

    public function testGetJournals(): void
    {
        $cal = new VCalendar();
        $cal->addChild(new Vjournal());

        $this->assertCount(1, $cal->getJournals());
    }

    public function testGetFreeBusy(): void
    {
        $cal = new VCalendar();
        $cal->addChild(new Vfreebusy());

        $this->assertCount(1, $cal->getFreeBusy());
    }

    public function testGetTimezones(): void
    {
        $cal = new VCalendar();
        $cal->addChild(new Vtimezone());
        $cal->addChild(new Vtimezone());

        $this->assertCount(2, $cal->getTimezones());
    }

    public function testGetEventsEmpty(): void
    {
        $cal = new VCalendar();
        $this->assertSame([], $cal->getEvents());
    }

    public function testParseFromString(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Test//EN',
            'BEGIN:VEVENT',
            'UID:event1@example.com',
            'SUMMARY:Meeting',
            'END:VEVENT',
            'BEGIN:VTODO',
            'UID:todo1@example.com',
            'SUMMARY:Task',
            'END:VTODO',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);

        $this->assertInstanceOf(VCalendar::class, $cal);
        $this->assertSame('2.0', $cal->getProperties()->getValue('VERSION'));

        assert($cal instanceof VCalendar);
        $this->assertCount(1, $cal->getEvents());
        $this->assertCount(1, $cal->getTodos());

        $event = $cal->getEvents()[0];
        $this->assertInstanceOf(Vevent::class, $event);
        $this->assertSame('Meeting', $event->getProperties()->getValue('SUMMARY'));
    }

    public function testParseWithTimezone(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VTIMEZONE',
            'TZID:America/New_York',
            'BEGIN:STANDARD',
            'DTSTART:19701101T020000',
            'TZOFFSETFROM:-0400',
            'TZOFFSETTO:-0500',
            'END:STANDARD',
            'BEGIN:DAYLIGHT',
            'DTSTART:19700308T020000',
            'TZOFFSETFROM:-0500',
            'TZOFFSETTO:-0400',
            'END:DAYLIGHT',
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'DTSTART;TZID=America/New_York:20260520T090000',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        assert($cal instanceof VCalendar);

        $this->assertCount(1, $cal->getTimezones());
        $this->assertCount(1, $cal->getEvents());

        $tz = $cal->getTimezones()[0];
        $this->assertInstanceOf(Vtimezone::class, $tz);
        $this->assertSame('America/New_York', $tz->getProperties()->getValue('TZID'));
        $this->assertNotNull($tz->getStandard());
        $this->assertNotNull($tz->getDaylight());
    }

    public function testRoundTrip(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Test//EN',
            'BEGIN:VEVENT',
            'UID:roundtrip@example.com',
            'SUMMARY:Test',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame($input, $cal->toString());
    }
}
