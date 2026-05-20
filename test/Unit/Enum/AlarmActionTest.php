<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Enum;

use Horde\Icalendar\Enum\AlarmAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlarmAction::class)]
class AlarmActionTest extends TestCase
{
    public function testKnownValues(): void
    {
        $known = ['AUDIO', 'DISPLAY', 'EMAIL'];

        foreach ($known as $val) {
            $action = AlarmAction::from($val);
            $this->assertTrue($action->isKnown());
        }
    }

    public function testUnknownPreservesValue(): void
    {
        $action = AlarmAction::from('X-SMS');
        $this->assertFalse($action->isKnown());
        $this->assertSame('X-SMS', $action->value);
    }
}
