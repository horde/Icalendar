<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Parser;

use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Parser\ParseError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentLine::class)]
class ContentLineTest extends TestCase
{
    public function testConstruction(): void
    {
        $params = ['TZID' => new Parameter('TZID', ['America/New_York'])];
        $cl = new ContentLine('item1', 'TEL', $params, '+1234567890', 5, 120);

        $this->assertSame('item1', $cl->group);
        $this->assertSame('TEL', $cl->name);
        $this->assertSame($params, $cl->parameters);
        $this->assertSame('+1234567890', $cl->value);
        $this->assertSame(5, $cl->lineNumber);
        $this->assertSame(120, $cl->byteOffset);
        $this->assertSame([], $cl->errors);
    }

    public function testNoGroup(): void
    {
        $cl = new ContentLine(null, 'VERSION', [], '2.0', 1, 0);

        $this->assertNull($cl->group);
    }

    public function testHasParameter(): void
    {
        $params = ['TZID' => new Parameter('TZID', ['UTC'])];
        $cl = new ContentLine(null, 'DTSTART', $params, '20260520T090000Z', 1, 0);

        $this->assertTrue($cl->hasParameter('TZID'));
        $this->assertTrue($cl->hasParameter('tzid'));
        $this->assertFalse($cl->hasParameter('VALUE'));
    }

    public function testGetParameter(): void
    {
        $param = new Parameter('VALUE', ['DATE']);
        $cl = new ContentLine(null, 'DTSTART', ['VALUE' => $param], '20260520', 1, 0);

        $this->assertSame($param, $cl->getParameter('VALUE'));
        $this->assertSame($param, $cl->getParameter('value'));
        $this->assertNull($cl->getParameter('TZID'));
    }

    public function testHasErrors(): void
    {
        $clean = new ContentLine(null, 'VERSION', [], '2.0', 1, 0);
        $this->assertFalse($clean->hasErrors());

        $errored = new ContentLine(null, '', [], '', 1, 0, [ParseError::InvalidName]);
        $this->assertTrue($errored->hasErrors());
    }
}
