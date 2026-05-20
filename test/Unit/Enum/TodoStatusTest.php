<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\TodoStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TodoStatus::class)]
class TodoStatusTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['NEEDS-ACTION', 'COMPLETED', 'IN-PROCESS', 'CANCELLED'];

        foreach ($known as $val) {
            $status = TodoStatus::from($val);
            $this->assertTrue($status->isKnown());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $status = TodoStatus::from('X-DEFERRED');
        $this->assertFalse($status->isKnown());
        $this->assertSame('X-DEFERRED', $status->value);
    }
}
