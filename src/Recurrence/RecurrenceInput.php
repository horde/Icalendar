<?php

declare(strict_types=1);

/**
 * Copyright 2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2026 The Horde Project
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Recurrence;

use DateTimeImmutable;

/**
 * Bundles the iCalendar properties needed for recurrence expansion.
 *
 * This value object decouples the expander interface from the component
 * model, making it independently testable.
 */
final readonly class RecurrenceInput
{
    /**
     * @param DateTimeImmutable $dtstart Event/task start date-time
     * @param string|null $rrule RFC 5545 RRULE string (null = no recurrence rule)
     * @param list<DateTimeImmutable> $exdates Excluded occurrence dates (EXDATE)
     * @param list<DateTimeImmutable> $rdates Additional occurrence dates beyond RRULE (RDATE)
     */
    public function __construct(
        public DateTimeImmutable $dtstart,
        public ?string $rrule = null,
        public array $exdates = [],
        public array $rdates = [],
    ) {}
}
