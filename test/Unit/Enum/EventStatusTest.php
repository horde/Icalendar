<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\EventStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventStatus::class)]
class EventStatusTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['TENTATIVE', 'CONFIRMED', 'CANCELLED'];

        foreach ($known as $val) {
            $status = EventStatus::from($val);
            $this->assertTrue($status->isKnown());
            $this->assertSame($val, $status->effective());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $status = EventStatus::from('X-CUSTOM');
        $this->assertFalse($status->isKnown());
        $this->assertSame('X-CUSTOM', $status->effective());
    }

    public function testCaseNormalization(): void
    {
        $status = EventStatus::from('confirmed');
        $this->assertSame('CONFIRMED', $status->value);
    }
}
