<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit;

use Horde\Icalendar\Calendar\VCalendar;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Reader;
use Horde\Icalendar\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Writer::class)]
class WriterTest extends TestCase
{
    public function testWriteString(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Test//Test//EN',
            'BEGIN:VEVENT',
            'UID:writer-test@example.com',
            'SUMMARY:Writer Test',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        assert($cal instanceof VCalendar);

        $writer = new Writer();
        $output = $writer->writeString($cal);

        $this->assertSame($input, $output);
    }

    public function testWriteStream(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:stream-test@example.com',
            'SUMMARY:Stream Test',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        assert($cal instanceof VCalendar);

        $stream = fopen('php://memory', 'r+');
        $writer = new Writer();
        $writer->writeStream($cal, $stream);

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        $this->assertSame($input, $output);
    }
}
