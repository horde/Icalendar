<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Calendar;

use DateTimeImmutable;
use DateTimeZone;
use Horde\Icalendar\Calendar\Valarm;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Enum\Classification;
use Horde\Icalendar\Enum\EventStatus;
use Horde\Icalendar\Enum\ParticipationStatus;
use Horde\Icalendar\Enum\Transparency;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\Organizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vevent::class)]
class VeventTypedAccessorTest extends TestCase
{
    private function parseEvent(string $ical): Vevent
    {
        $component = AbstractComponent::fromString($ical);
        assert($component instanceof Vevent);
        return $component;
    }

    public function testScalarStringProperties(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test-uid-123@example.com',
            'SUMMARY:Team Meeting',
            'DESCRIPTION:Weekly sync',
            'LOCATION:Room 42',
            'URL:https://example.com/meeting',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame('test-uid-123@example.com', $event->getUid());
        $this->assertSame('Team Meeting', $event->getSummary());
        $this->assertSame('Weekly sync', $event->getDescription());
        $this->assertSame('Room 42', $event->getLocation());
        $this->assertSame('https://example.com/meeting', $event->getUrl());
    }

    public function testScalarStringSetters(): void
    {
        $event = new Vevent();
        $event->setUid('new-uid@test');
        $event->setSummary('New Event');
        $event->setDescription('A description');
        $event->setLocation('Here');
        $event->setUrl('https://example.com');

        $this->assertSame('new-uid@test', $event->getUid());
        $this->assertSame('New Event', $event->getSummary());
        $this->assertSame('A description', $event->getDescription());
        $this->assertSame('Here', $event->getLocation());
        $this->assertSame('https://example.com', $event->getUrl());
    }

    public function testIntegerProperties(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'SEQUENCE:3',
            'PRIORITY:5',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame(3, $event->getSequence());
        $this->assertSame(5, $event->getPriority());
    }

    public function testIntegerDefaults(): void
    {
        $event = new Vevent();
        $this->assertSame(0, $event->getSequence());
        $this->assertSame(0, $event->getPriority());
    }

    public function testEnumProperties(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'STATUS:CONFIRMED',
            'CLASS:PRIVATE',
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame('CONFIRMED', $event->getStatus()->value);
        $this->assertSame('PRIVATE', $event->getClassification()->value);
        $this->assertSame('TRANSPARENT', $event->getTransparency()->value);
    }

    public function testEnumDefaults(): void
    {
        $event = new Vevent();
        $this->assertNull($event->getStatus());
        $this->assertSame('PUBLIC', $event->getClassification()->value);
        $this->assertSame('OPAQUE', $event->getTransparency()->value);
    }

    public function testEnumSetters(): void
    {
        $event = new Vevent();
        $event->setStatus(EventStatus::from('TENTATIVE'));
        $event->setClassification(Classification::from('CONFIDENTIAL'));
        $event->setTransparency(Transparency::from('TRANSPARENT'));

        $this->assertSame('TENTATIVE', $event->getStatus()->value);
        $this->assertSame('CONFIDENTIAL', $event->getClassification()->value);
        $this->assertSame('TRANSPARENT', $event->getTransparency()->value);
    }

    public function testDateTimeUtc(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'DTSTART:20260520T090000Z',
            'DTEND:20260520T100000Z',
            'DTSTAMP:20260515T120000Z',
            'CREATED:20260510T080000Z',
            'LAST-MODIFIED:20260518T140000Z',
            'END:VEVENT',
            '',
        ]));

        $dtstart = $event->getDtstart();
        $this->assertNotNull($dtstart);
        $this->assertSame('2026-05-20 09:00:00', $dtstart->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $dtstart->getTimezone()->getName());

        $dtend = $event->getDtend();
        $this->assertNotNull($dtend);
        $this->assertSame('2026-05-20 10:00:00', $dtend->format('Y-m-d H:i:s'));

        $this->assertNotNull($event->getDtstamp());
        $this->assertNotNull($event->getCreated());
        $this->assertNotNull($event->getLastModified());
    }

    public function testDateTimeWithTzid(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'DTSTART;TZID=America/New_York:20260520T090000',
            'DTEND;TZID=America/New_York:20260520T100000',
            'END:VEVENT',
            '',
        ]));

        $dtstart = $event->getDtstart();
        $this->assertNotNull($dtstart);
        $this->assertSame('2026-05-20 09:00:00', $dtstart->format('Y-m-d H:i:s'));
        $this->assertSame('America/New_York', $dtstart->getTimezone()->getName());
    }

    public function testDateTimeSetters(): void
    {
        $event = new Vevent();
        $dt = new DateTimeImmutable('2026-06-15 14:00:00', new DateTimeZone('UTC'));
        $event->setDtstart($dt);
        $event->setDtend($dt->modify('+1 hour'));

        $this->assertSame('2026-06-15 14:00:00', $event->getDtstart()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-15 15:00:00', $event->getDtend()->format('Y-m-d H:i:s'));
    }

    public function testDateTimeSetterWithTzid(): void
    {
        $event = new Vevent();
        $dt = new DateTimeImmutable('2026-06-15 09:00:00', new DateTimeZone('America/New_York'));
        $event->setDtstart($dt, 'America/New_York');

        $prop = $event->getProperties()->get('DTSTART');
        $this->assertSame('20260615T090000', $prop->getValue());
        $this->assertSame('America/New_York', $prop->getParameter('TZID')?->getValue());
    }

    public function testAttendees(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'ATTENDEE;CN=Alice;PARTSTAT=ACCEPTED;ROLE=REQ-PARTICIPANT:mailto:alice@example.com',
            'ATTENDEE;CN=Bob;PARTSTAT=TENTATIVE;ROLE=OPT-PARTICIPANT;RSVP=TRUE:mailto:bob@example.com',
            'END:VEVENT',
            '',
        ]));

        $attendees = $event->getAttendees();
        $this->assertCount(2, $attendees);

        $alice = $attendees[0];
        $this->assertSame('alice@example.com', $alice->getEmail());
        $this->assertSame('Alice', $alice->getCommonName());
        $this->assertSame('ACCEPTED', $alice->getParticipationStatus()->value);
        $this->assertSame('REQ-PARTICIPANT', $alice->getRole()->value);

        $bob = $attendees[1];
        $this->assertSame('bob@example.com', $bob->getEmail());
        $this->assertSame('Bob', $bob->getCommonName());
        $this->assertSame('TENTATIVE', $bob->getParticipationStatus()->value);
        $this->assertSame('OPT-PARTICIPANT', $bob->getRole()->value);
        $this->assertTrue($bob->getRsvp());
    }

    public function testAddAttendee(): void
    {
        $event = new Vevent();
        $event->addAttendee(Attendee::create(
            'carol@example.com',
            cn: 'Carol',
            partstat: ParticipationStatus::from('NEEDS-ACTION'),
        ));

        $attendees = $event->getAttendees();
        $this->assertCount(1, $attendees);
        $this->assertSame('carol@example.com', $attendees[0]->getEmail());
        $this->assertSame('Carol', $attendees[0]->getCommonName());
    }

    public function testOrganizer(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'ORGANIZER;CN=Boss:mailto:boss@example.com',
            'END:VEVENT',
            '',
        ]));

        $org = $event->getOrganizer();
        $this->assertNotNull($org);
        $this->assertSame('boss@example.com', $org->getEmail());
        $this->assertSame('Boss', $org->getCommonName());
    }

    public function testSetOrganizer(): void
    {
        $event = new Vevent();
        $event->setOrganizer(Organizer::create('leader@example.com', cn: 'Team Lead'));

        $org = $event->getOrganizer();
        $this->assertNotNull($org);
        $this->assertSame('leader@example.com', $org->getEmail());
        $this->assertSame('Team Lead', $org->getCommonName());
    }

    public function testRecurrence(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR',
            'EXDATE:20260525T090000Z,20260527T090000Z',
            'RDATE:20260601T090000Z',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame('FREQ=WEEKLY;BYDAY=MO,WE,FR', $event->getRrule());
        $this->assertSame(['20260525T090000Z', '20260527T090000Z'], $event->getExdateValues());
        $this->assertSame(['20260601T090000Z'], $event->getRdateValues());
    }

    public function testCategories(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'CATEGORIES:Work,Meeting,Important',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame(['Work', 'Meeting', 'Important'], $event->getCategories());
    }

    public function testCategoriesEmpty(): void
    {
        $event = new Vevent();
        $this->assertSame([], $event->getCategories());
    }

    public function testSetCategories(): void
    {
        $event = new Vevent();
        $event->setCategories(['Personal', 'Health']);
        $this->assertSame(['Personal', 'Health'], $event->getCategories());
    }

    public function testDuration(): void
    {
        $event = $this->parseEvent(implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:test@example.com',
            'DURATION:PT1H30M',
            'END:VEVENT',
            '',
        ]));

        $this->assertSame('PT1H30M', $event->getDuration());
    }

    public function testNullReturnsForAbsentProperties(): void
    {
        $event = new Vevent();
        $this->assertNull($event->getUid());
        $this->assertNull($event->getSummary());
        $this->assertNull($event->getDescription());
        $this->assertNull($event->getLocation());
        $this->assertNull($event->getUrl());
        $this->assertNull($event->getStatus());
        $this->assertNull($event->getDtstart());
        $this->assertNull($event->getDtend());
        $this->assertNull($event->getDuration());
        $this->assertNull($event->getCreated());
        $this->assertNull($event->getLastModified());
        $this->assertNull($event->getDtstamp());
        $this->assertNull($event->getOrganizer());
        $this->assertNull($event->getRrule());
        $this->assertSame([], $event->getAttendees());
        $this->assertSame([], $event->getExdateValues());
        $this->assertSame([], $event->getRdateValues());
    }

    public function testFullRoundTrip(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VEVENT',
            'UID:roundtrip@example.com',
            'SUMMARY:Round Trip Test',
            'DTSTART:20260520T090000Z',
            'DTEND:20260520T100000Z',
            'STATUS:CONFIRMED',
            'SEQUENCE:2',
            'END:VEVENT',
            '',
        ]);

        $event = $this->parseEvent($input);

        $this->assertSame('roundtrip@example.com', $event->getUid());
        $this->assertSame('Round Trip Test', $event->getSummary());
        $this->assertSame('CONFIRMED', $event->getStatus()->value);
        $this->assertSame(2, $event->getSequence());

        $this->assertSame($input, $event->toString());
    }
}
