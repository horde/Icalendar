<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\ScheduleAgent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScheduleAgent::class)]
class ScheduleAgentTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['SERVER', 'CLIENT', 'NONE'];

        foreach ($known as $val) {
            $agent = ScheduleAgent::from($val);
            $this->assertTrue($agent->isKnown());
            $this->assertSame($val, $agent->effective());
        }
    }

    public function testUnknownFallsBackToServer(): void
    {
        $agent = ScheduleAgent::from('X-CUSTOM');
        $this->assertFalse($agent->isKnown());
        $this->assertSame('SERVER', $agent->effective());
    }

    public function testCaseNormalization(): void
    {
        $agent = ScheduleAgent::from('client');
        $this->assertSame('CLIENT', $agent->value);
    }
}
