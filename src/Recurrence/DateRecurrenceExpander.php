<?php

declare(strict_types=1);

/**
 * Copyright 2003-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author    Jan Schneider <jan@horde.org>
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Ralf Lang <ralf.lang@ralf-lang.de>
 * @category  Horde
 * @copyright 2003-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Recurrence;

use DateTimeImmutable;
use DateTimeInterface;
use Horde\Date\Recurrence\Recurrence;

/**
 * Default RecurrenceExpander backed by Horde\Date\Recurrence\Recurrence.
 *
 * Bridges iCalendar properties (DTSTART, RRULE, EXDATE, RDATE) to
 * the date-math engine in horde/date. Dependency direction is
 * one-way: icalendar → date.
 */
final class DateRecurrenceExpander implements RecurrenceExpander
{
    public function expand(RecurrenceInput $input, DateTimeInterface $from, DateTimeInterface $until): OccurrenceSet
    {
        $dates = [];

        if ($input->rrule !== null) {
            $recurrence = new Recurrence($input->dtstart);
            $recurrence->fromRRule20($input->rrule);

            foreach ($input->exdates as $exdate) {
                $recurrence->addException($exdate);
            }

            // Iterate through occurrences using nextActiveRecurrence.
            // The Recurrence engine uses day-level arithmetic, so we must
            // advance by +1 day (not +1 second) to find the next occurrence.
            $fromImmutable = $from instanceof DateTimeImmutable
                ? $from
                : DateTimeImmutable::createFromInterface($from);
            $after = ($fromImmutable > $input->dtstart) ? $fromImmutable->modify('-1 day') : $input->dtstart->modify('-1 day');
            while (($next = $recurrence->nextActiveRecurrence($after)) !== null) {
                if ($next > $until) {
                    break;
                }
                if ($next >= $from) {
                    $dates[] = $next;
                }
                $after = $next->modify('+1 day');
            }
        } else {
            // No RRULE — DTSTART is the only base occurrence
            if ($input->dtstart >= $from && $input->dtstart <= $until) {
                $exdateStrings = array_map(
                    fn(DateTimeImmutable $d) => $d->format('Ymd'),
                    $input->exdates,
                );
                if (!in_array($input->dtstart->format('Ymd'), $exdateStrings, true)) {
                    $dates[] = $input->dtstart;
                }
            }
        }

        // Merge RDATEs within range that are not excluded
        $exdateStrings ??= array_map(
            fn(DateTimeImmutable $d) => $d->format('Ymd'),
            $input->exdates,
        );

        foreach ($input->rdates as $rdate) {
            if ($rdate >= $from && $rdate <= $until) {
                if (!in_array($rdate->format('Ymd'), $exdateStrings, true)) {
                    $dates[] = $rdate;
                }
            }
        }

        // Deduplicate by formatted timestamp
        $seen = [];
        $unique = [];
        foreach ($dates as $date) {
            $key = $date->format('Y-m-d H:i:s');
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $date;
            }
        }

        return new OccurrenceSet($unique);
    }
}
