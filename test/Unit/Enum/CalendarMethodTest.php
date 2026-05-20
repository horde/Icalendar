<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\CalendarMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarMethod::class)]
class CalendarMethodTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = [
            'PUBLISH', 'REQUEST', 'REPLY', 'ADD',
            'CANCEL', 'REFRESH', 'COUNTER', 'DECLINECOUNTER',
        ];

        foreach ($known as $val) {
            $method = CalendarMethod::from($val);
            $this->assertTrue($method->isKnown());
            $this->assertSame($val, $method->effective());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $method = CalendarMethod::from('X-CUSTOM-METHOD');
        $this->assertFalse($method->isKnown());
        $this->assertSame('X-CUSTOM-METHOD', $method->value);
    }

    public function testCaseNormalization(): void
    {
        $method = CalendarMethod::from('request');
        $this->assertSame('REQUEST', $method->value);
    }
}
