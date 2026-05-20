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

    // =========================================================================
    // Scalar string properties
    // =========================================================================

    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    public function getSummary(): ?string
    {
        return $this->getPropertyValue('SUMMARY');
    }

    public function setSummary(string $summary): void
    {
        $this->setProperty('SUMMARY', $summary);
    }

    public function getDescription(): ?string
    {
        return $this->getPropertyValue('DESCRIPTION');
    }

    public function setDescription(string $description): void
    {
        $this->setProperty('DESCRIPTION', $description);
    }

    public function getLocation(): ?string
    {
        return $this->getPropertyValue('LOCATION');
    }

    public function setLocation(string $location): void
    {
        $this->setProperty('LOCATION', $location);
    }

    // =========================================================================
    // Integer properties
    // =========================================================================

    public function getPriority(): int
    {
        $raw = $this->getPropertyValue('PRIORITY');
        return $raw !== null ? (int) $raw : 0;
    }

    public function setPriority(int $priority): void
    {
        $this->setProperty('PRIORITY', (string) $priority);
    }

    public function getPercentComplete(): int
    {
        $raw = $this->getPropertyValue('PERCENT-COMPLETE');
        return $raw !== null ? (int) $raw : 0;
    }

    public function setPercentComplete(int $percent): void
    {
        $this->setProperty('PERCENT-COMPLETE', (string) $percent);
    }

    public function getSequence(): int
    {
        $raw = $this->getPropertyValue('SEQUENCE');
        return $raw !== null ? (int) $raw : 0;
    }

    public function setSequence(int $sequence): void
    {
        $this->setProperty('SEQUENCE', (string) $sequence);
    }

    // =========================================================================
    // Enum properties
    // =========================================================================

    public function getStatus(): ?TodoStatus
    {
        $raw = $this->getPropertyValue('STATUS');
        return $raw !== null ? TodoStatus::from($raw) : null;
    }

    public function setStatus(TodoStatus $status): void
    {
        $this->setProperty('STATUS', $status->value);
    }

    public function getClassification(): Classification
    {
        $raw = $this->getPropertyValue('CLASS');
        return $raw !== null ? Classification::from($raw) : Classification::from('PUBLIC');
    }

    public function setClassification(Classification $class): void
    {
        $this->setProperty('CLASS', $class->value);
    }

    // =========================================================================
    // Date/time properties
    // =========================================================================

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

    public function getDue(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DUE');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    public function setDue(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DUE', DateTimeParser::format($dt, $tzid), $params);
    }

    public function getCompleted(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('COMPLETED');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    public function setCompleted(DateTimeImmutable $dt): void
    {
        $this->setProperty('COMPLETED', DateTimeParser::format($dt));
    }

    public function getDtstamp(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTAMP');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    // =========================================================================
    // Attendee / Organizer
    // =========================================================================

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

    // =========================================================================
    // Recurrence
    // =========================================================================

    public function getRrule(): ?string
    {
        return $this->getPropertyValue('RRULE');
    }

    public function setRrule(string $rrule): void
    {
        $this->setProperty('RRULE', $rrule);
    }

    // =========================================================================
    // Multi-value properties
    // =========================================================================

    /** @return list<string> */
    public function getCategories(): array
    {
        $raw = $this->getPropertyValue('CATEGORIES');
        if ($raw === null) {
            return [];
        }
        return array_map('trim', explode(',', $raw));
    }

    public function setCategories(array $categories): void
    {
        $this->setProperty('CATEGORIES', implode(',', $categories));
    }
}
