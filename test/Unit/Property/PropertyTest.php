<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Property;

use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Property::class)]
class PropertyTest extends TestCase
{
    public function testConstruction(): void
    {
        $params = ['TZID' => new Parameter('TZID', ['America/New_York'])];
        $prop = new Property('dtstart', '20260520T090000', $params, null);

        $this->assertSame('DTSTART', $prop->getName());
        $this->assertNull($prop->getGroup());
        $this->assertSame('20260520T090000', $prop->getValue());
        $this->assertSame($params, $prop->getParameters());
    }

    public function testNameUppercased(): void
    {
        $prop = new Property('summary', 'Hello');

        $this->assertSame('SUMMARY', $prop->getName());
    }

    public function testGroupPreserved(): void
    {
        $prop = new Property('TEL', '+1234567890', [], 'item1');

        $this->assertSame('item1', $prop->getGroup());
    }

    public function testFromContentLine(): void
    {
        $cl = new ContentLine(
            'item2',
            'X-ABLABEL',
            ['VALUE' => new Parameter('VALUE', ['TEXT'])],
            'Home',
            5,
            120,
        );

        $prop = Property::fromContentLine($cl);

        $this->assertSame('X-ABLABEL', $prop->getName());
        $this->assertSame('item2', $prop->getGroup());
        $this->assertSame('Home', $prop->getValue());
        $this->assertSame('TEXT', $prop->getParameter('VALUE')->getValue());
    }

    public function testGetParameter(): void
    {
        $param = new Parameter('TZID', ['Europe/Berlin']);
        $prop = new Property('DTSTART', '20260520T090000', ['TZID' => $param]);

        $this->assertSame($param, $prop->getParameter('TZID'));
        $this->assertSame($param, $prop->getParameter('tzid'));
        $this->assertNull($prop->getParameter('VALUE'));
    }

    public function testHasParameter(): void
    {
        $prop = new Property('DTSTART', '20260520', ['VALUE' => new Parameter('VALUE', ['DATE'])]);

        $this->assertTrue($prop->hasParameter('VALUE'));
        $this->assertTrue($prop->hasParameter('value'));
        $this->assertFalse($prop->hasParameter('TZID'));
    }

    public function testSetValue(): void
    {
        $prop = new Property('SUMMARY', 'Old');
        $prop->setValue('New');

        $this->assertSame('New', $prop->getValue());
    }

    public function testSetParameter(): void
    {
        $prop = new Property('DTSTART', '20260520T090000');
        $param = new Parameter('TZID', ['UTC']);
        $prop->setParameter('TZID', $param);

        $this->assertTrue($prop->hasParameter('TZID'));
        $this->assertSame($param, $prop->getParameter('TZID'));
    }

    public function testRemoveParameter(): void
    {
        $prop = new Property('DTSTART', '20260520T090000', [
            'TZID' => new Parameter('TZID', ['UTC']),
        ]);

        $prop->removeParameter('tzid');
        $this->assertFalse($prop->hasParameter('TZID'));
    }

    public function testToContentLineSimple(): void
    {
        $prop = new Property('VERSION', '2.0');

        $this->assertSame('VERSION:2.0', $prop->toContentLine());
    }

    public function testToContentLineWithParams(): void
    {
        $prop = new Property('DTSTART', '20260520T090000', [
            'TZID' => new Parameter('TZID', ['America/New_York']),
            'VALUE' => new Parameter('VALUE', ['DATE-TIME']),
        ]);

        $this->assertSame(
            'DTSTART;TZID=America/New_York;VALUE=DATE-TIME:20260520T090000',
            $prop->toContentLine(),
        );
    }

    public function testToContentLineWithGroup(): void
    {
        $prop = new Property('TEL', '+1234567890', [], 'item1');

        $this->assertSame('item1.TEL:+1234567890', $prop->toContentLine());
    }

    public function testToContentLineNakedParam(): void
    {
        $prop = new Property('TEL', '+1234567890', [
            'HOME' => new Parameter('HOME', ['']),
        ]);

        $this->assertSame('TEL;HOME:+1234567890', $prop->toContentLine());
    }

    public function testToContentLineQuotedParamValue(): void
    {
        $prop = new Property('ORGANIZER', 'mailto:boss@example.com', [
            'CN' => new Parameter('CN', ['Foo; Bar']),
        ]);

        $this->assertSame(
            'ORGANIZER;CN="Foo; Bar":mailto:boss@example.com',
            $prop->toContentLine(),
        );
    }

    public function testToContentLineMultiValueParam(): void
    {
        $prop = new Property('TEL', '+1234567890', [
            'TYPE' => new Parameter('TYPE', ['WORK', 'VOICE']),
        ]);

        $this->assertSame('TEL;TYPE=WORK,VOICE:+1234567890', $prop->toContentLine());
    }
}
