<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\AttendeeRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttendeeRole::class)]
class AttendeeRoleTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['CHAIR', 'REQ-PARTICIPANT', 'OPT-PARTICIPANT', 'NON-PARTICIPANT'];

        foreach ($known as $val) {
            $role = AttendeeRole::from($val);
            $this->assertTrue($role->isKnown());
            $this->assertSame($val, $role->effective());
        }
    }

    public function testUnknownFallsBackToDefault(): void
    {
        $role = AttendeeRole::from('X-CUSTOM-ROLE');
        $this->assertFalse($role->isKnown());
        $this->assertSame('REQ-PARTICIPANT', $role->effective());
    }

    public function testCaseNormalization(): void
    {
        $role = AttendeeRole::from('chair');
        $this->assertSame('CHAIR', $role->value);
        $this->assertTrue($role->isKnown());
    }

    public function testEquals(): void
    {
        $a = AttendeeRole::from('CHAIR');
        $b = AttendeeRole::from('chair');
        $this->assertTrue($a->equals($b));
    }
}
