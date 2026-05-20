<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Property;

use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;
use Horde\Icalendar\Property\PropertyBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyBag::class)]
class PropertyBagTest extends TestCase
{
    // =========================================================================
    // Construction
    // =========================================================================

    public function testEmptyBag(): void
    {
        $bag = new PropertyBag();

        $this->assertSame([], $bag->all());
        $this->assertSame([], $bag->names());
        $this->assertFalse($bag->has('VERSION'));
    }

    public function testFromContentLines(): void
    {
        $lines = [
            new ContentLine(null, 'VERSION', [], '2.0', 1, 0),
            new ContentLine(null, 'PRODID', [], '-//Test//EN', 2, 12),
        ];

        $bag = PropertyBag::fromContentLines($lines);

        $this->assertSame('2.0', $bag->getValue('VERSION'));
        $this->assertSame('-//Test//EN', $bag->getValue('PRODID'));
    }

    // =========================================================================
    // Single-occurrence queries
    // =========================================================================

    public function testGetReturnsFirstOccurrence(): void
    {
        $bag = new PropertyBag();
        $bag->add('ATTENDEE', 'mailto:first@example.com');
        $bag->add('ATTENDEE', 'mailto:second@example.com');

        $this->assertSame('mailto:first@example.com', $bag->get('ATTENDEE')->getValue());
    }

    public function testGetReturnsNullIfMissing(): void
    {
        $bag = new PropertyBag();
        $this->assertNull($bag->get('NONEXISTENT'));
    }

    public function testGetValueReturnsNullIfMissing(): void
    {
        $bag = new PropertyBag();
        $this->assertNull($bag->getValue('NONEXISTENT'));
    }

    public function testCaseInsensitiveLookup(): void
    {
        $bag = new PropertyBag();
        $bag->set('VERSION', '2.0');

        $this->assertSame('2.0', $bag->getValue('version'));
        $this->assertSame('2.0', $bag->getValue('Version'));
        $this->assertTrue($bag->has('VERSION'));
        $this->assertTrue($bag->has('version'));
    }

    // =========================================================================
    // Multi-occurrence queries
    // =========================================================================

    public function testGetAll(): void
    {
        $bag = new PropertyBag();
        $bag->add('ATTENDEE', 'mailto:a@example.com', [
            'ROLE' => new Parameter('ROLE', ['REQ-PARTICIPANT']),
        ]);
        $bag->add('ATTENDEE', 'mailto:b@example.com', [
            'ROLE' => new Parameter('ROLE', ['OPT-PARTICIPANT']),
        ]);

        $all = $bag->getAll('ATTENDEE');
        $this->assertCount(2, $all);
        $this->assertSame('mailto:a@example.com', $all[0]->getValue());
        $this->assertSame('mailto:b@example.com', $all[1]->getValue());
    }

    public function testGetAllValues(): void
    {
        $bag = new PropertyBag();
        $bag->add('CATEGORIES', 'Work');
        $bag->add('CATEGORIES', 'Meeting');

        $this->assertSame(['Work', 'Meeting'], $bag->getAllValues('CATEGORIES'));
    }

    public function testCount(): void
    {
        $bag = new PropertyBag();
        $this->assertSame(0, $bag->count('ATTENDEE'));

        $bag->add('ATTENDEE', 'mailto:a@example.com');
        $bag->add('ATTENDEE', 'mailto:b@example.com');

        $this->assertSame(2, $bag->count('ATTENDEE'));
    }

    // =========================================================================
    // Set vs Add semantics
    // =========================================================================

    public function testSetReplacesAll(): void
    {
        $bag = new PropertyBag();
        $bag->add('SUMMARY', 'Old 1');
        $bag->add('SUMMARY', 'Old 2');

        $bag->set('SUMMARY', 'New');

        $this->assertSame(1, $bag->count('SUMMARY'));
        $this->assertSame('New', $bag->getValue('SUMMARY'));
    }

    public function testAddAppends(): void
    {
        $bag = new PropertyBag();
        $bag->add('EXDATE', '20260520');
        $bag->add('EXDATE', '20260521');

        $this->assertSame(2, $bag->count('EXDATE'));
    }

    public function testSetDoesNotAffectOtherProperties(): void
    {
        $bag = new PropertyBag();
        $bag->add('VERSION', '2.0');
        $bag->add('PRODID', '-//Test//EN');
        $bag->set('VERSION', '3.0');

        $this->assertSame('3.0', $bag->getValue('VERSION'));
        $this->assertSame('-//Test//EN', $bag->getValue('PRODID'));
    }

    // =========================================================================
    // Remove
    // =========================================================================

    public function testRemoveAll(): void
    {
        $bag = new PropertyBag();
        $bag->add('ATTENDEE', 'mailto:a@example.com');
        $bag->add('ATTENDEE', 'mailto:b@example.com');
        $bag->add('SUMMARY', 'Meeting');

        $bag->remove('ATTENDEE');

        $this->assertFalse($bag->has('ATTENDEE'));
        $this->assertTrue($bag->has('SUMMARY'));
    }

    public function testRemoveAt(): void
    {
        $bag = new PropertyBag();
        $bag->add('ATTENDEE', 'mailto:a@example.com');
        $bag->add('ATTENDEE', 'mailto:b@example.com');
        $bag->add('ATTENDEE', 'mailto:c@example.com');

        $bag->removeAt('ATTENDEE', 1);

        $values = $bag->getAllValues('ATTENDEE');
        $this->assertSame(['mailto:a@example.com', 'mailto:c@example.com'], $values);
    }

    public function testRemoveAtOutOfBounds(): void
    {
        $bag = new PropertyBag();
        $bag->add('VERSION', '2.0');

        $bag->removeAt('VERSION', 5);

        $this->assertTrue($bag->has('VERSION'));
    }

    // =========================================================================
    // Ordering
    // =========================================================================

    public function testOrderingPreserved(): void
    {
        $bag = new PropertyBag();
        $bag->add('BEGIN', 'VEVENT');
        $bag->add('UID', 'abc@example.com');
        $bag->add('DTSTART', '20260520T090000Z');
        $bag->add('SUMMARY', 'Test');
        $bag->add('END', 'VEVENT');

        $names = array_map(fn(Property $p) => $p->getName(), $bag->all());
        $this->assertSame(['BEGIN', 'UID', 'DTSTART', 'SUMMARY', 'END'], $names);
    }

    public function testNames(): void
    {
        $bag = new PropertyBag();
        $bag->add('VERSION', '2.0');
        $bag->add('ATTENDEE', 'mailto:a@example.com');
        $bag->add('ATTENDEE', 'mailto:b@example.com');
        $bag->add('SUMMARY', 'Test');

        $names = $bag->names();
        $this->assertSame(['VERSION', 'ATTENDEE', 'SUMMARY'], $names);
    }

    // =========================================================================
    // Serialization
    // =========================================================================

    public function testToContentLines(): void
    {
        $bag = new PropertyBag();
        $bag->add('VERSION', '2.0');
        $bag->add('PRODID', '-//Test//EN');

        $lines = $bag->toContentLines();

        $this->assertSame(['VERSION:2.0', 'PRODID:-//Test//EN'], $lines);
    }

    // =========================================================================
    // Round-trip: parse → bag → serialize
    // =========================================================================

    public function testRoundTrip(): void
    {
        $contentLines = [
            new ContentLine(null, 'VERSION', [], '2.0', 1, 0),
            new ContentLine(null, 'DTSTART', [
                'TZID' => new Parameter('TZID', ['America/New_York']),
            ], '20260520T090000', 2, 12),
            new ContentLine(null, 'ATTENDEE', [
                'CN' => new Parameter('CN', ['Alice']),
                'ROLE' => new Parameter('ROLE', ['REQ-PARTICIPANT']),
            ], 'mailto:alice@example.com', 3, 55),
        ];

        $bag = PropertyBag::fromContentLines($contentLines);
        $serialized = $bag->toContentLines();

        $this->assertSame('VERSION:2.0', $serialized[0]);
        $this->assertSame('DTSTART;TZID=America/New_York:20260520T090000', $serialized[1]);
        $this->assertSame('ATTENDEE;CN=Alice;ROLE=REQ-PARTICIPANT:mailto:alice@example.com', $serialized[2]);
    }

    // =========================================================================
    // AddProperty
    // =========================================================================

    public function testAddProperty(): void
    {
        $bag = new PropertyBag();
        $prop = new Property('SUMMARY', 'Hello');
        $bag->addProperty($prop);

        $this->assertSame($prop, $bag->get('SUMMARY'));
    }

    // =========================================================================
    // Multi-occurrence with parameters scenario
    // =========================================================================

    public function testAttendeeScenario(): void
    {
        $bag = new PropertyBag();
        $bag->add('ATTENDEE', 'mailto:organizer@example.com', [
            'CN' => new Parameter('CN', ['Boss']),
            'ROLE' => new Parameter('ROLE', ['CHAIR']),
            'PARTSTAT' => new Parameter('PARTSTAT', ['ACCEPTED']),
        ]);
        $bag->add('ATTENDEE', 'mailto:worker@example.com', [
            'CN' => new Parameter('CN', ['Worker']),
            'ROLE' => new Parameter('ROLE', ['REQ-PARTICIPANT']),
            'PARTSTAT' => new Parameter('PARTSTAT', ['NEEDS-ACTION']),
        ]);

        $attendees = $bag->getAll('ATTENDEE');
        $this->assertCount(2, $attendees);

        $this->assertSame('Boss', $attendees[0]->getParameter('CN')->getValue());
        $this->assertSame('CHAIR', $attendees[0]->getParameter('ROLE')->getValue());

        $this->assertSame('Worker', $attendees[1]->getParameter('CN')->getValue());
        $this->assertSame('NEEDS-ACTION', $attendees[1]->getParameter('PARTSTAT')->getValue());
    }
}
