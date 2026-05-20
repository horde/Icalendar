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

use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Enum\AlarmAction;
use Horde\Icalendar\Value\Attendee;

class Valarm extends AbstractComponent
{
    public function getType(): string
    {
        return 'VALARM';
    }

    public function getAction(): ?AlarmAction
    {
        $raw = $this->getPropertyValue('ACTION');
        return $raw !== null ? AlarmAction::from($raw) : null;
    }

    public function setAction(AlarmAction $action): void
    {
        $this->setProperty('ACTION', $action->value);
    }

    public function getTrigger(): ?string
    {
        return $this->getPropertyValue('TRIGGER');
    }

    public function setTrigger(string $trigger): void
    {
        $this->setProperty('TRIGGER', $trigger);
    }

    public function getDescription(): ?string
    {
        return $this->getPropertyValue('DESCRIPTION');
    }

    public function setDescription(string $description): void
    {
        $this->setProperty('DESCRIPTION', $description);
    }

    public function getSummary(): ?string
    {
        return $this->getPropertyValue('SUMMARY');
    }

    public function setSummary(string $summary): void
    {
        $this->setProperty('SUMMARY', $summary);
    }

    public function getDuration(): ?string
    {
        return $this->getPropertyValue('DURATION');
    }

    public function setDuration(string $duration): void
    {
        $this->setProperty('DURATION', $duration);
    }

    public function getRepeat(): int
    {
        $raw = $this->getPropertyValue('REPEAT');
        return $raw !== null ? (int) $raw : 0;
    }

    public function setRepeat(int $repeat): void
    {
        $this->setProperty('REPEAT', (string) $repeat);
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
}
