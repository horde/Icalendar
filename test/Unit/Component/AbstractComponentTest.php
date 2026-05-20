<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Component;

use Horde\Icalendar\Component;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Component\GenericComponent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\PropertyBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractComponent::class)]
#[CoversClass(GenericComponent::class)]
class AbstractComponentTest extends TestCase
{
    // =========================================================================
    // Construction
    // =========================================================================

    public function testConstructWithoutPropertyBag(): void
    {
        $component = new GenericComponent('VEVENT');

        $this->assertSame('VEVENT', $component->getType());
        $this->assertInstanceOf(PropertyBag::class, $component->getProperties());
        $this->assertSame([], $component->getProperties()->all());
    }

    public function testConstructWithPropertyBag(): void
    {
        $bag = new PropertyBag();
        $bag->add('SUMMARY', 'Test Event');

        $component = new GenericComponent('VEVENT', $bag);

        $this->assertSame('Test Event', $component->getPropertyValue('SUMMARY'));
    }

    public function testTypeIsUppercased(): void
    {
        $component = new GenericComponent('vevent');

        $this->assertSame('VEVENT', $component->getType());
    }

    // =========================================================================
    // Property delegation
    // =========================================================================

    public function testGetPropertyValue(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('SUMMARY', 'Meeting');

        $this->assertSame('Meeting', $component->getPropertyValue('SUMMARY'));
    }

    public function testGetPropertyValueReturnsNullIfMissing(): void
    {
        $component = new GenericComponent('VEVENT');

        $this->assertNull($component->getPropertyValue('SUMMARY'));
    }

    public function testSetPropertyReplaces(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('SUMMARY', 'First');
        $component->setProperty('SUMMARY', 'Second');

        $this->assertSame('Second', $component->getPropertyValue('SUMMARY'));
        $this->assertSame(1, $component->getProperties()->count('SUMMARY'));
    }

    public function testAddPropertyAppends(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->addProperty('ATTENDEE', 'mailto:a@example.com');
        $component->addProperty('ATTENDEE', 'mailto:b@example.com');

        $this->assertSame(2, $component->getProperties()->count('ATTENDEE'));
    }

    public function testSetPropertyWithParameters(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('DTSTART', '20260520T090000', [
            'TZID' => new Parameter('TZID', ['America/New_York']),
        ]);

        $prop = $component->getProperties()->get('DTSTART');
        $this->assertSame('America/New_York', $prop->getParameter('TZID')->getValue());
    }

    public function testRemoveProperty(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('SUMMARY', 'Test');
        $component->removeProperty('SUMMARY');

        $this->assertNull($component->getPropertyValue('SUMMARY'));
    }

    // =========================================================================
    // Children
    // =========================================================================

    public function testAddChild(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $event = new GenericComponent('VEVENT');

        $calendar->addChild($event);

        $this->assertCount(1, $calendar->getChildren());
        $this->assertSame($event, $calendar->getChildren()[0]);
    }

    public function testGetChildrenByType(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $event1 = new GenericComponent('VEVENT');
        $event2 = new GenericComponent('VEVENT');
        $todo = new GenericComponent('VTODO');

        $calendar->addChild($event1);
        $calendar->addChild($todo);
        $calendar->addChild($event2);

        $events = $calendar->getChildrenByType('VEVENT');
        $this->assertCount(2, $events);
        $this->assertSame($event1, $events[0]);
        $this->assertSame($event2, $events[1]);
    }

    public function testGetChildrenByTypeCaseInsensitive(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $event = new GenericComponent('VEVENT');
        $calendar->addChild($event);

        $this->assertCount(1, $calendar->getChildrenByType('vevent'));
    }

    public function testGetFirstChild(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $event1 = new GenericComponent('VEVENT');
        $event2 = new GenericComponent('VEVENT');
        $calendar->addChild($event1);
        $calendar->addChild($event2);

        $this->assertSame($event1, $calendar->getFirstChild('VEVENT'));
    }

    public function testGetFirstChildReturnsNullIfNone(): void
    {
        $calendar = new GenericComponent('VCALENDAR');

        $this->assertNull($calendar->getFirstChild('VEVENT'));
    }

    public function testRemoveChild(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $event1 = new GenericComponent('VEVENT');
        $event2 = new GenericComponent('VEVENT');
        $calendar->addChild($event1);
        $calendar->addChild($event2);

        $calendar->removeChild($event1);

        $children = $calendar->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame($event2, $children[0]);
    }

    // =========================================================================
    // Serialization
    // =========================================================================

    public function testToStringMinimal(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('UID', 'abc@example.com');
        $component->setProperty('SUMMARY', 'Test');

        $expected = "BEGIN:VEVENT\r\nUID:abc@example.com\r\nSUMMARY:Test\r\nEND:VEVENT\r\n";
        $this->assertSame($expected, $component->toString());
    }

    public function testToStringWithChildren(): void
    {
        $calendar = new GenericComponent('VCALENDAR');
        $calendar->setProperty('VERSION', '2.0');

        $event = new GenericComponent('VEVENT');
        $event->setProperty('UID', 'test@example.com');
        $calendar->addChild($event);

        $output = $calendar->toString();

        $this->assertStringContainsString("BEGIN:VCALENDAR\r\n", $output);
        $this->assertStringContainsString("VERSION:2.0\r\n", $output);
        $this->assertStringContainsString("BEGIN:VEVENT\r\n", $output);
        $this->assertStringContainsString("UID:test@example.com\r\n", $output);
        $this->assertStringContainsString("END:VEVENT\r\n", $output);
        $this->assertStringContainsString("END:VCALENDAR\r\n", $output);
    }

    public function testToStringFoldsLongLines(): void
    {
        $component = new GenericComponent('VEVENT');
        $longValue = str_repeat('A', 100);
        $component->setProperty('DESCRIPTION', $longValue);

        $output = $component->toString();
        $lines = explode("\r\n", $output);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }
    }

    public function testMagicToString(): void
    {
        $component = new GenericComponent('VEVENT');
        $component->setProperty('UID', 'x@y.com');

        $this->assertSame($component->toString(), (string) $component);
    }

    // =========================================================================
    // Parsing
    // =========================================================================

    public function testFromStringSimple(): void
    {
        $input = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nEND:VCALENDAR\r\n";

        $component = AbstractComponent::fromString($input);

        $this->assertSame('VCALENDAR', $component->getType());
        $this->assertSame('2.0', $component->getProperties()->getValue('VERSION'));
        $this->assertSame('-//Test//EN', $component->getProperties()->getValue('PRODID'));
    }

    public function testFromStringWithNestedComponents(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:event1@example.com',
            'SUMMARY:Test Event',
            'END:VEVENT',
            'BEGIN:VTODO',
            'UID:todo1@example.com',
            'END:VTODO',
            'END:VCALENDAR',
            '',
        ]);

        $component = AbstractComponent::fromString($input);

        $this->assertSame('VCALENDAR', $component->getType());
        $this->assertCount(2, $component->getChildren());

        $event = $component->getChildren()[0];
        $this->assertSame('VEVENT', $event->getType());
        $this->assertSame('event1@example.com', $event->getProperties()->getValue('UID'));
        $this->assertSame('Test Event', $event->getProperties()->getValue('SUMMARY'));

        $todo = $component->getChildren()[1];
        $this->assertSame('VTODO', $todo->getType());
        $this->assertSame('todo1@example.com', $todo->getProperties()->getValue('UID'));
    }

    public function testFromStringDeeplyNested(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:event@example.com',
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'TRIGGER:-PT15M',
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $component = AbstractComponent::fromString($input);
        $event = $component->getChildren()[0];
        $alarm = $event->getChildren()[0];

        $this->assertSame('VALARM', $alarm->getType());
        $this->assertSame('DISPLAY', $alarm->getProperties()->getValue('ACTION'));
        $this->assertSame('-PT15M', $alarm->getProperties()->getValue('TRIGGER'));
    }

    public function testFromStringWithParameters(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VEVENT',
            'DTSTART;TZID=America/New_York:20260520T090000',
            'END:VEVENT',
            '',
        ]);

        $component = AbstractComponent::fromString($input);
        $prop = $component->getProperties()->get('DTSTART');

        $this->assertSame('20260520T090000', $prop->getValue());
        $this->assertSame('America/New_York', $prop->getParameter('TZID')->getValue());
    }

    public function testFromStringEmptyInputThrows(): void
    {
        $this->expectException(\Horde\Icalendar\Parser\ParseException::class);
        AbstractComponent::fromString('');
    }

    public function testFromStringMissingBeginThrows(): void
    {
        $this->expectException(\Horde\Icalendar\Parser\ParseException::class);
        AbstractComponent::fromString("VERSION:2.0\r\nEND:VCALENDAR\r\n");
    }

    public function testFromStringUnterminatedThrows(): void
    {
        $this->expectException(\Horde\Icalendar\Parser\ParseException::class);
        AbstractComponent::fromString("BEGIN:VCALENDAR\r\nVERSION:2.0\r\n");
    }

    // =========================================================================
    // Round-trip
    // =========================================================================

    public function testRoundTrip(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Test//EN',
            'BEGIN:VEVENT',
            'UID:round-trip@example.com',
            'SUMMARY:Round Trip Test',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $component = AbstractComponent::fromString($input);
        $output = $component->toString();

        $this->assertSame($input, $output);
    }
}
