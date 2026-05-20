<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\Classification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Classification::class)]
class ClassificationTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['PUBLIC', 'PRIVATE', 'CONFIDENTIAL'];

        foreach ($known as $val) {
            $class = Classification::from($val);
            $this->assertTrue($class->isKnown());
            $this->assertSame($val, $class->effective());
        }
    }

    public function testUnknownFallsBackToPublic(): void
    {
        $class = Classification::from('X-SECRET');
        $this->assertFalse($class->isKnown());
        $this->assertSame('PUBLIC', $class->effective());
    }
}
