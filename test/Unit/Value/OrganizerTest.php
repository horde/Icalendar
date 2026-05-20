<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Value;

use Horde\Icalendar\Enum\ScheduleAgent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;
use Horde\Icalendar\Value\Organizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Organizer::class)]
class OrganizerTest extends TestCase
{
    public function testCreateMinimal(): void
    {
        $org = Organizer::create('boss@example.com');
        $this->assertSame('boss@example.com', $org->getEmail());
        $this->assertNull($org->getCommonName());
        $this->assertNull($org->getSentBy());
        $this->assertSame('SERVER', $org->getScheduleAgent()->value);
    }

    public function testCreateFull(): void
    {
        $org = Organizer::create(
            'boss@example.com',
            cn: 'The Boss',
            sentBy: 'assistant@example.com',
            scheduleAgent: ScheduleAgent::from('CLIENT'),
        );

        $this->assertSame('boss@example.com', $org->getEmail());
        $this->assertSame('The Boss', $org->getCommonName());
        $this->assertSame('assistant@example.com', $org->getSentBy());
        $this->assertSame('CLIENT', $org->getScheduleAgent()->value);
    }

    public function testFromProperty(): void
    {
        $prop = new Property('ORGANIZER', 'mailto:organizer@example.com', [
            'CN' => new Parameter('CN', ['Meeting Organizer']),
            'SENT-BY' => new Parameter('SENT-BY', ['mailto:secretary@example.com']),
        ]);

        $org = new Organizer($prop);
        $this->assertSame('organizer@example.com', $org->getEmail());
        $this->assertSame('Meeting Organizer', $org->getCommonName());
        $this->assertSame('secretary@example.com', $org->getSentBy());
    }

    public function testSetters(): void
    {
        $org = Organizer::create('test@example.com');

        $org->setCommonName('Test User');
        $this->assertSame('Test User', $org->getCommonName());

        $org->setSentBy('proxy@example.com');
        $this->assertSame('proxy@example.com', $org->getSentBy());

        $org->setScheduleAgent(ScheduleAgent::from('NONE'));
        $this->assertSame('NONE', $org->getScheduleAgent()->value);
    }

    public function testSetterMutatesUnderlyingProperty(): void
    {
        $prop = new Property('ORGANIZER', 'mailto:test@example.com', []);
        $org = new Organizer($prop);

        $org->setCommonName('Updated');
        $this->assertSame('Updated', $prop->getParameter('CN')?->getValue());
    }

    public function testEmailWithoutMailto(): void
    {
        $prop = new Property('ORGANIZER', 'someone@example.com', []);
        $org = new Organizer($prop);
        $this->assertSame('someone@example.com', $org->getEmail());
    }

    public function testSerialization(): void
    {
        $org = Organizer::create('boss@example.com', cn: 'The Boss');
        $line = $org->getProperty()->toContentLine();
        $this->assertStringContainsString('ORGANIZER', $line);
        $this->assertStringContainsString('mailto:boss@example.com', $line);
        $this->assertStringContainsString('CN=The Boss', $line);
    }
}
