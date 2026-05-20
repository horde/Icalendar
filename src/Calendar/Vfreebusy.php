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

use DateTimeImmutable;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\DateTimeParser;
use Horde\Icalendar\Value\Organizer;

class Vfreebusy extends AbstractComponent
{
    public function getType(): string
    {
        return 'VFREEBUSY';
    }

    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    public function getDtstart(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTART');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    public function setDtstart(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTSTART', DateTimeParser::format($dt, $tzid), $params);
    }

    public function getDtend(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTEND');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    public function setDtend(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTEND', DateTimeParser::format($dt, $tzid), $params);
    }

    /** @return list<Attendee> */
    public function getAttendees(): array
    {
        return array_map(
            fn($p) => new Attendee($p),
            $this->getProperties()->getAll('ATTENDEE'),
        );
    }

    public function addAttendee(Attendee $attendee): void
    {
        $this->getProperties()->addProperty($attendee->getProperty());
    }

    public function getOrganizer(): ?Organizer
    {
        $prop = $this->getProperties()->get('ORGANIZER');
        return $prop !== null ? new Organizer($prop) : null;
    }

    public function setOrganizer(Organizer $organizer): void
    {
        $this->removeProperty('ORGANIZER');
        $this->getProperties()->addProperty($organizer->getProperty());
    }

    /** @return list<string> */
    public function getFreebusyValues(): array
    {
        return $this->getProperties()->getAllValues('FREEBUSY');
    }
}
