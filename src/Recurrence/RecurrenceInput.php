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
