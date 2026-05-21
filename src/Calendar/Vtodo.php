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
use Horde\Icalendar\Component;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Enum\Classification;
use Horde\Icalendar\Enum\TodoStatus;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\DateTimeParser;
use Horde\Icalendar\Value\Organizer;

class Vtodo extends AbstractComponent
{
    /** Get the iCalendar component type identifier. */
    public function getType(): string
    {
        return 'VTODO';
    }

    /** @return list<Valarm> */
    public function getAlarms(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Valarm,
        ));
    }

    /** @section Scalar string properties */

    /** Get the todo UID. */
    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    /** Set the todo UID. */
    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    /** Get the todo summary. */
    public function getSummary(): ?string
    {
        return $this->getPropertyValue('SUMMARY');
    }

    /** Set the todo summary. */
    public function setSummary(string $summary): void
    {
        $this->setProperty('SUMMARY', $summary);
    }

    /** Get the todo description. */
    public function getDescription(): ?string
    {
        return $this->getPropertyValue('DESCRIPTION');
    }

    /** Set the todo description. */
    public function setDescription(string $description): void
    {
        $this->setProperty('DESCRIPTION', $description);
    }

    /** Get the todo location. */
    public function getLocation(): ?string
    {
        return $this->getPropertyValue('LOCATION');
    }

    /** Set the todo location. */
    public function setLocation(string $location): void
    {
        $this->setProperty('LOCATION', $location);
    }

    /** @section Integer properties */

    /** Get the todo priority (0 = undefined, 1 = highest, 9 = lowest). */
    public function getPriority(): int
    {
        $raw = $this->getPropertyValue('PRIORITY');
        return $raw !== null ? (int) $raw : 0;
    }

    /** Set the todo priority. */
    public function setPriority(int $priority): void
    {
        $this->setProperty('PRIORITY', (string) $priority);
    }

    /** Get the percentage of completion (0-100). */
    public function getPercentComplete(): int
    {
        $raw = $this->getPropertyValue('PERCENT-COMPLETE');
        return $raw !== null ? (int) $raw : 0;
    }

    /** Set the percentage of completion. */
    public function setPercentComplete(int $percent): void
    {
        $this->setProperty('PERCENT-COMPLETE', (string) $percent);
    }

    /** Get the todo revision sequence number. */
    public function getSequence(): int
    {
        $raw = $this->getPropertyValue('SEQUENCE');
        return $raw !== null ? (int) $raw : 0;
    }

    /** Set the todo revision sequence number. */
    public function setSequence(int $sequence): void
    {
        $this->setProperty('SEQUENCE', (string) $sequence);
    }

    /** @section Enum properties */

    /** Get the todo status. */
    public function getStatus(): ?TodoStatus
    {
        $raw = $this->getPropertyValue('STATUS');
        return $raw !== null ? TodoStatus::from($raw) : null;
    }

    /** Set the todo status. */
    public function setStatus(TodoStatus $status): void
    {
        $this->setProperty('STATUS', $status->value);
    }

    /** Get the todo access classification, defaults to PUBLIC. */
    public function getClassification(): Classification
    {
        $raw = $this->getPropertyValue('CLASS');
        return $raw !== null ? Classification::from($raw) : Classification::from('PUBLIC');
    }

    /** Set the todo access classification. */
    public function setClassification(Classification $class): void
    {
        $this->setProperty('CLASS', $class->value);
    }

    /** @section Date/time properties */

    /** Get the todo start date/time. */
    public function getDtstart(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTART');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    /** Set the todo start date/time. */
    public function setDtstart(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTSTART', DateTimeParser::format($dt, $tzid), $params);
    }

    /** Get the todo due date/time. */
    public function getDue(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DUE');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    /** Set the todo due date/time. */
    public function setDue(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DUE', DateTimeParser::format($dt, $tzid), $params);
    }

    /** Get the date/time the todo was completed. */
    public function getCompleted(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('COMPLETED');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    /** Set the date/time the todo was completed. */
    public function setCompleted(DateTimeImmutable $dt): void
    {
        $this->setProperty('COMPLETED', DateTimeParser::format($dt));
    }

    /** Get the date/time stamp of the todo. */
    public function getDtstamp(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTAMP');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    /** @section Attendee and Organizer */

    /** @return list<Attendee> */
    public function getAttendees(): array
    {
        return array_map(
            fn($p) => new Attendee($p),
            $this->getProperties()->getAll('ATTENDEE'),
        );
    }

    /** Add an attendee to the todo. */
    public function addAttendee(Attendee $attendee): void
    {
        $this->getProperties()->addProperty($attendee->getProperty());
    }

    /** Get the todo organizer. */
    public function getOrganizer(): ?Organizer
    {
        $prop = $this->getProperties()->get('ORGANIZER');
        return $prop !== null ? new Organizer($prop) : null;
    }

    /** Set the todo organizer, replacing any existing one. */
    public function setOrganizer(Organizer $organizer): void
    {
        $this->removeProperty('ORGANIZER');
        $this->getProperties()->addProperty($organizer->getProperty());
    }

    /** @section Recurrence */

    /** Get the recurrence rule. */
    public function getRrule(): ?string
    {
        return $this->getPropertyValue('RRULE');
    }

    /** Set the recurrence rule. */
    public function setRrule(string $rrule): void
    {
        $this->setProperty('RRULE', $rrule);
    }

    /** @section Multi-value properties */

    /** @return list<string> */
    public function getCategories(): array
    {
        $raw = $this->getPropertyValue('CATEGORIES');
        if ($raw === null) {
            return [];
        }
        return array_map('trim', explode(',', $raw));
    }

    /** Set the todo categories. */
    public function setCategories(array $categories): void
    {
        $this->setProperty('CATEGORIES', implode(',', $categories));
    }
}
