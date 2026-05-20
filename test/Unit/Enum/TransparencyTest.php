<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\Transparency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Transparency::class)]
class TransparencyTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['OPAQUE', 'TRANSPARENT'];

        foreach ($known as $val) {
            $transp = Transparency::from($val);
            $this->assertTrue($transp->isKnown());
            $this->assertSame($val, $transp->effective());
        }
    }

    public function testUnknownFallsBackToOpaque(): void
    {
        $transp = Transparency::from('X-CUSTOM');
        $this->assertFalse($transp->isKnown());
        $this->assertSame('OPAQUE', $transp->effective());
    }
}
