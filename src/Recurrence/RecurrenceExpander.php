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

use DateTimeInterface;

/**
 * Expands iCalendar recurrence rules into a set of occurrence dates.
 *
 * Given a RecurrenceInput (DTSTART + RRULE + EXDATE + RDATE) and a
 * date range, produces an OccurrenceSet of all occurrences within
 * that range.
 */
interface RecurrenceExpander
{
    /**
     * Expand recurrence into a set of occurrences within a date range.
     *
     * The range is inclusive on both ends: occurrences on exactly $from
     * or $until are included.
     */
    public function expand(RecurrenceInput $input, DateTimeInterface $from, DateTimeInterface $until): OccurrenceSet;
}
