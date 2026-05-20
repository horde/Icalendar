<?php

declare(strict_types=1);

/**
 * Copyright 2003-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author    Mike Cochrane <mike@graftonhall.co.nz>
 * @author    Jan Schneider <jan@horde.org>
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Ralf Lang <ralf.lang@ralf-lang.de>
 * @category  Horde
 * @copyright 2003-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Calendar;

use Horde\Icalendar\Component;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Enum\CalendarMethod;
use Horde\Icalendar\RootComponent;

class VCalendar extends AbstractComponent implements RootComponent
{
    public function getType(): string
    {
        return 'VCALENDAR';
    }

    // =========================================================================
    // Calendar-level properties
    // =========================================================================

    public function getVersion(): ?string
    {
        return $this->getPropertyValue('VERSION');
    }

    public function setVersion(string $version = '2.0'): void
    {
        $this->setProperty('VERSION', $version);
    }

    public function getProdid(): ?string
    {
        return $this->getPropertyValue('PRODID');
    }

    public function setProdid(string $prodid): void
    {
        $this->setProperty('PRODID', $prodid);
    }

    public function getCalscale(): ?string
    {
        return $this->getPropertyValue('CALSCALE');
    }

    public function setCalscale(string $calscale = 'GREGORIAN'): void
    {
        $this->setProperty('CALSCALE', $calscale);
    }

    public function getMethod(): ?CalendarMethod
    {
        $raw = $this->getPropertyValue('METHOD');
        return $raw !== null ? CalendarMethod::from($raw) : null;
    }

    public function setMethod(CalendarMethod $method): void
    {
        $this->setProperty('METHOD', $method->value);
    }

    // =========================================================================
    // Typed child component accessors
    // =========================================================================

    /** @return list<Vevent> */
    public function getEvents(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Vevent,
        ));
    }

    /** @return list<Vtodo> */
    public function getTodos(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Vtodo,
        ));
    }

    /** @return list<Vjournal> */
    public function getJournals(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Vjournal,
        ));
    }

    /** @return list<Vfreebusy> */
    public function getFreeBusy(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Vfreebusy,
        ));
    }

    /** @return list<Vtimezone> */
    public function getTimezones(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Vtimezone,
        ));
    }
}
