<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\CuType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CuType::class)]
class CuTypeTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['INDIVIDUAL', 'GROUP', 'RESOURCE', 'ROOM', 'UNKNOWN'];

        foreach ($known as $val) {
            $cutype = CuType::from($val);
            $this->assertTrue($cutype->isKnown());
            $this->assertSame($val, $cutype->effective());
        }
    }

    public function testUnknownFallsBackToIndividual(): void
    {
        $cutype = CuType::from('X-DEVICE');
        $this->assertFalse($cutype->isKnown());
        $this->assertSame('INDIVIDUAL', $cutype->effective());
    }

    public function testCaseNormalization(): void
    {
        $cutype = CuType::from('room');
        $this->assertSame('ROOM', $cutype->value);
    }
}
