<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Calendar;

use DateTimeImmutable;
use DateTimeZone;
use Horde\Icalendar\Calendar\Vtodo;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Enum\TodoStatus;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\Organizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vtodo::class)]
class VtodoTypedAccessorTest extends TestCase
{
    public function testParseAndAccessProperties(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VTODO',
            'UID:todo-123@example.com',
            'SUMMARY:Buy groceries',
            'DESCRIPTION:Milk, eggs, bread',
            'STATUS:IN-PROCESS',
            'PRIORITY:3',
            'PERCENT-COMPLETE:50',
            'DUE:20260525T170000Z',
            'CATEGORIES:Personal,Shopping',
            'END:VTODO',
            '',
        ]);

        $todo = AbstractComponent::fromString($input);
        assert($todo instanceof Vtodo);

        $this->assertSame('todo-123@example.com', $todo->getUid());
        $this->assertSame('Buy groceries', $todo->getSummary());
        $this->assertSame('Milk, eggs, bread', $todo->getDescription());
        $this->assertSame('IN-PROCESS', $todo->getStatus()->value);
        $this->assertSame(3, $todo->getPriority());
        $this->assertSame(50, $todo->getPercentComplete());
        $this->assertSame(['Personal', 'Shopping'], $todo->getCategories());

        $due = $todo->getDue();
        $this->assertNotNull($due);
        $this->assertSame('2026-05-25 17:00:00', $due->format('Y-m-d H:i:s'));
    }

    public function testSetters(): void
    {
        $todo = new Vtodo();
        $todo->setUid('new-todo@test');
        $todo->setSummary('Task name');
        $todo->setStatus(TodoStatus::from('COMPLETED'));
        $todo->setPercentComplete(100);
        $todo->setCompleted(new DateTimeImmutable('2026-05-20 10:00:00', new DateTimeZone('UTC')));

        $this->assertSame('new-todo@test', $todo->getUid());
        $this->assertSame('Task name', $todo->getSummary());
        $this->assertSame('COMPLETED', $todo->getStatus()->value);
        $this->assertSame(100, $todo->getPercentComplete());
        $this->assertNotNull($todo->getCompleted());
    }

    public function testAttendeeAndOrganizer(): void
    {
        $todo = new Vtodo();
        $todo->setOrganizer(Organizer::create('manager@example.com', cn: 'Manager'));
        $todo->addAttendee(Attendee::create('worker@example.com', cn: 'Worker'));

        $org = $todo->getOrganizer();
        $this->assertNotNull($org);
        $this->assertSame('manager@example.com', $org->getEmail());

        $attendees = $todo->getAttendees();
        $this->assertCount(1, $attendees);
        $this->assertSame('worker@example.com', $attendees[0]->getEmail());
    }

    public function testDefaults(): void
    {
        $todo = new Vtodo();
        $this->assertNull($todo->getStatus());
        $this->assertSame(0, $todo->getPriority());
        $this->assertSame(0, $todo->getPercentComplete());
        $this->assertSame('PUBLIC', $todo->getClassification()->value);
        $this->assertSame([], $todo->getCategories());
    }

    public function testRoundTrip(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VTODO',
            'UID:todo-rt@example.com',
            'SUMMARY:Round Trip',
            'STATUS:NEEDS-ACTION',
            'PRIORITY:1',
            'END:VTODO',
            '',
        ]);

        $todo = AbstractComponent::fromString($input);
        $this->assertSame($input, $todo->toString());
    }
}
