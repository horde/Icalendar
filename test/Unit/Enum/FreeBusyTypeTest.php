<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\FreeBusyType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FreeBusyType::class)]
class FreeBusyTypeTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['FREE', 'BUSY', 'BUSY-UNAVAILABLE', 'BUSY-TENTATIVE'];

        foreach ($known as $val) {
            $fbtype = FreeBusyType::from($val);
            $this->assertTrue($fbtype->isKnown());
            $this->assertSame($val, $fbtype->effective());
        }
    }

    public function testUnknownFallsBackToBusy(): void
    {
        $fbtype = FreeBusyType::from('X-CUSTOM');
        $this->assertFalse($fbtype->isKnown());
        $this->assertSame('BUSY', $fbtype->effective());
    }
}
