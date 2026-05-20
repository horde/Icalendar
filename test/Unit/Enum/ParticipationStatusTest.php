<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\ParticipationStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParticipationStatus::class)]
class ParticipationStatusTest extends TestCase
{
    public function testKnownValue(): void
    {
        $status = ParticipationStatus::from('ACCEPTED');
        $this->assertSame('ACCEPTED', $status->value);
        $this->assertTrue($status->isKnown());
        $this->assertSame('ACCEPTED', $status->effective());
    }

    public function testAllKnownValues(): void
    {
        $known = [
            'NEEDS-ACTION', 'ACCEPTED', 'DECLINED', 'TENTATIVE',
            'DELEGATED', 'COMPLETED', 'IN-PROCESS',
        ];

        foreach ($known as $val) {
            $status = ParticipationStatus::from($val);
            $this->assertTrue($status->isKnown(), "Expected $val to be known");
            $this->assertSame($val, $status->effective());
        }
    }

    public function testUnknownValue(): void
    {
        $status = ParticipationStatus::from('X-CUSTOM');
        $this->assertSame('X-CUSTOM', $status->value);
        $this->assertFalse($status->isKnown());
        $this->assertSame('NEEDS-ACTION', $status->effective());
    }

    public function testCaseNormalization(): void
    {
        $status = ParticipationStatus::from('accepted');
        $this->assertSame('ACCEPTED', $status->value);
        $this->assertTrue($status->isKnown());
    }

    public function testToString(): void
    {
        $status = ParticipationStatus::from('TENTATIVE');
        $this->assertSame('TENTATIVE', $status->toString());
    }

    public function testEquals(): void
    {
        $a = ParticipationStatus::from('ACCEPTED');
        $b = ParticipationStatus::from('ACCEPTED');
        $c = ParticipationStatus::from('DECLINED');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
