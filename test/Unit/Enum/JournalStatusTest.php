<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\JournalStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JournalStatus::class)]
class JournalStatusTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['DRAFT', 'FINAL', 'CANCELLED'];

        foreach ($known as $val) {
            $status = JournalStatus::from($val);
            $this->assertTrue($status->isKnown());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $status = JournalStatus::from('X-REVIEW');
        $this->assertFalse($status->isKnown());
        $this->assertSame('X-REVIEW', $status->value);
    }
}
