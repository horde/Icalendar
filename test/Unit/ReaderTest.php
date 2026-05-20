<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit;

use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Calendar\Vtodo;
use Horde\Icalendar\Calendar\Vtimezone;
use Horde\Icalendar\Reader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
class ReaderTest extends TestCase
{
    public function testStreamComponentsYieldsEvents(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Test//Test//EN',
            'BEGIN:VEVENT',
            'UID:event1@example.com',
            'SUMMARY:First Event',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'UID:event2@example.com',
            'SUMMARY:Second Event',
            'END:VEVENT',
            'BEGIN:VTODO',
            'UID:todo1@example.com',
            'SUMMARY:A Task',
            'END:VTODO',
            'END:VCALENDAR',
            '',
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);

        $reader = new Reader();
        $components = iterator_to_array($reader->streamComponents($stream));
        fclose($stream);

        $this->assertCount(3, $components);
        $this->assertInstanceOf(Vevent::class, $components[0]);
        $this->assertInstanceOf(Vevent::class, $components[1]);
        $this->assertInstanceOf(Vtodo::class, $components[2]);

        assert($components[0] instanceof Vevent);
        assert($components[1] instanceof Vevent);
        assert($components[2] instanceof Vtodo);

        $this->assertSame('event1@example.com', $components[0]->getUid());
        $this->assertSame('event2@example.com', $components[1]->getUid());
        $this->assertSame('todo1@example.com', $components[2]->getUid());
    }

    public function testStreamComponentsWithTimezone(): void
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
            'TZNAME:EST',
            'END:STANDARD',
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'UID:tz-test@example.com',
            'SUMMARY:Event with TZ',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);

        $reader = new Reader();
        $components = iterator_to_array($reader->streamComponents($stream));
        fclose($stream);

        $this->assertCount(2, $components);
        $this->assertInstanceOf(Vtimezone::class, $components[0]);
        $this->assertInstanceOf(Vevent::class, $components[1]);
    }

    public function testStreamComponentsEmpty(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'END:VCALENDAR',
            '',
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);

        $reader = new Reader();
        $components = iterator_to_array($reader->streamComponents($stream));
        fclose($stream);

        $this->assertCount(0, $components);
    }

    public function testReadFile(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:file-test@example.com',
            'SUMMARY:File Test',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'ical_test_');
        file_put_contents($tmpFile, $input);

        try {
            $reader = new Reader();
            $cal = $reader->readFile($tmpFile);
            $this->assertSame('VCALENDAR', $cal->getType());
        } finally {
            unlink($tmpFile);
        }
    }
}
