<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Parser;

use Horde\Icalendar\Parser\Parameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parameter::class)]
class ParameterTest extends TestCase
{
    public function testSingleValue(): void
    {
        $param = new Parameter('TZID', ['America/New_York']);

        $this->assertSame('TZID', $param->name);
        $this->assertSame(['America/New_York'], $param->values);
        $this->assertSame('America/New_York', $param->getValue());
        $this->assertFalse($param->isMultiValue());
    }

    public function testMultiValue(): void
    {
        $param = new Parameter('TYPE', ['WORK', 'VOICE']);

        $this->assertSame('TYPE', $param->name);
        $this->assertSame(['WORK', 'VOICE'], $param->values);
        $this->assertSame('WORK', $param->getValue());
        $this->assertTrue($param->isMultiValue());
    }

    public function testEmptyValues(): void
    {
        $param = new Parameter('HOME', ['']);

        $this->assertSame('', $param->getValue());
        $this->assertFalse($param->isMultiValue());
    }

    public function testValuesAreReindexed(): void
    {
        $param = new Parameter('X', [2 => 'a', 5 => 'b']);

        $this->assertSame(['a', 'b'], $param->values);
    }
}
