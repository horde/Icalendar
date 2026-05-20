<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\RelationshipType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelationshipType::class)]
class RelationshipTypeTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['PARENT', 'CHILD', 'SIBLING'];

        foreach ($known as $val) {
            $rel = RelationshipType::from($val);
            $this->assertTrue($rel->isKnown());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $rel = RelationshipType::from('X-DEPENDS-ON');
        $this->assertFalse($rel->isKnown());
        $this->assertSame('X-DEPENDS-ON', $rel->value);
    }
}
