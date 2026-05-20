<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Value;

use Horde\Icalendar\Enum\AttendeeRole;
use Horde\Icalendar\Enum\CuType;
use Horde\Icalendar\Enum\ParticipationStatus;
use Horde\Icalendar\Enum\ScheduleAgent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;
use Horde\Icalendar\Value\Attendee;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attendee::class)]
class AttendeeTest extends TestCase
{
    public function testCreateMinimal(): void
    {
        $attendee = Attendee::create('alice@example.com');
        $this->assertSame('alice@example.com', $attendee->getEmail());
        $this->assertNull($attendee->getCommonName());
        $this->assertSame('NEEDS-ACTION', $attendee->getParticipationStatus()->value);
        $this->assertSame('REQ-PARTICIPANT', $attendee->getRole()->value);
        $this->assertFalse($attendee->getRsvp());
        $this->assertSame('SERVER', $attendee->getScheduleAgent()->value);
        $this->assertSame('INDIVIDUAL', $attendee->getCuType()->value);
        $this->assertSame([], $attendee->getDelegatedTo());
        $this->assertSame([], $attendee->getDelegatedFrom());
    }

    public function testCreateFull(): void
    {
        $attendee = Attendee::create(
            'bob@example.com',
            cn: 'Bob Smith',
            partstat: ParticipationStatus::from('ACCEPTED'),
            role: AttendeeRole::from('OPT-PARTICIPANT'),
            rsvp: true,
            scheduleAgent: ScheduleAgent::from('CLIENT'),
            cutype: CuType::from('INDIVIDUAL'),
            delegatedTo: ['carol@example.com'],
            delegatedFrom: ['dave@example.com'],
        );

        $this->assertSame('bob@example.com', $attendee->getEmail());
        $this->assertSame('Bob Smith', $attendee->getCommonName());
        $this->assertSame('ACCEPTED', $attendee->getParticipationStatus()->value);
        $this->assertSame('OPT-PARTICIPANT', $attendee->getRole()->value);
        $this->assertTrue($attendee->getRsvp());
        $this->assertSame('CLIENT', $attendee->getScheduleAgent()->value);
        $this->assertSame('INDIVIDUAL', $attendee->getCuType()->value);
        $this->assertSame(['carol@example.com'], $attendee->getDelegatedTo());
        $this->assertSame(['dave@example.com'], $attendee->getDelegatedFrom());
    }

    public function testFromProperty(): void
    {
        $prop = new Property('ATTENDEE', 'mailto:jane@example.com', [
            'CN' => new Parameter('CN', ['Jane Doe']),
            'PARTSTAT' => new Parameter('PARTSTAT', ['TENTATIVE']),
            'ROLE' => new Parameter('ROLE', ['CHAIR']),
            'RSVP' => new Parameter('RSVP', ['TRUE']),
        ]);

        $attendee = new Attendee($prop);
        $this->assertSame('jane@example.com', $attendee->getEmail());
        $this->assertSame('Jane Doe', $attendee->getCommonName());
        $this->assertSame('TENTATIVE', $attendee->getParticipationStatus()->value);
        $this->assertSame('CHAIR', $attendee->getRole()->value);
        $this->assertTrue($attendee->getRsvp());
    }

    public function testSetters(): void
    {
        $attendee = Attendee::create('alice@example.com');

        $attendee->setParticipationStatus(ParticipationStatus::from('ACCEPTED'));
        $this->assertSame('ACCEPTED', $attendee->getParticipationStatus()->value);

        $attendee->setRole(AttendeeRole::from('OPT-PARTICIPANT'));
        $this->assertSame('OPT-PARTICIPANT', $attendee->getRole()->value);

        $attendee->setCommonName('Alice');
        $this->assertSame('Alice', $attendee->getCommonName());

        $attendee->setRsvp(true);
        $this->assertTrue($attendee->getRsvp());

        $attendee->setRsvp(false);
        $this->assertFalse($attendee->getRsvp());

        $attendee->setScheduleAgent(ScheduleAgent::from('NONE'));
        $this->assertSame('NONE', $attendee->getScheduleAgent()->value);
    }

    public function testSetterMutatesUnderlyingProperty(): void
    {
        $prop = new Property('ATTENDEE', 'mailto:test@example.com', []);
        $attendee = new Attendee($prop);

        $attendee->setParticipationStatus(ParticipationStatus::from('DECLINED'));

        $this->assertSame('DECLINED', $prop->getParameter('PARTSTAT')?->getValue());
    }

    public function testEmailWithoutMailto(): void
    {
        $prop = new Property('ATTENDEE', 'alice@example.com', []);
        $attendee = new Attendee($prop);
        $this->assertSame('alice@example.com', $attendee->getEmail());
    }

    public function testGetProperty(): void
    {
        $prop = new Property('ATTENDEE', 'mailto:test@example.com', []);
        $attendee = new Attendee($prop);
        $this->assertSame($prop, $attendee->getProperty());
    }

    public function testSerialization(): void
    {
        $attendee = Attendee::create(
            'bob@example.com',
            cn: 'Bob Smith',
            partstat: ParticipationStatus::from('ACCEPTED'),
            role: AttendeeRole::from('REQ-PARTICIPANT'),
            rsvp: true,
        );

        $line = $attendee->getProperty()->toContentLine();
        $this->assertStringContainsString('ATTENDEE', $line);
        $this->assertStringContainsString('mailto:bob@example.com', $line);
        $this->assertStringContainsString('CN=Bob Smith', $line);
        $this->assertStringContainsString('PARTSTAT=ACCEPTED', $line);
        $this->assertStringContainsString('ROLE=REQ-PARTICIPANT', $line);
        $this->assertStringContainsString('RSVP=TRUE', $line);
    }
}
