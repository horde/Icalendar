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
use Horde\Icalendar\Enum\EventStatus;
use Horde\Icalendar\Enum\Transparency;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\DateTimeParser;
use Horde\Icalendar\Value\Organizer;

class Vevent extends AbstractComponent
{
    /** Get the iCalendar component type identifier. */
    public function getType(): string
    {
        return 'VEVENT';
    }

    /** @section Child component accessors */

    /** @return list<Valarm> */
    public function getAlarms(): array
    {
        return array_values(array_filter(
            $this->getChildren(),
            fn(Component $c): bool => $c instanceof Valarm,
        ));
    }

    /** @section Scalar string properties */

    /** Get the event UID. */
    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    /** Set the event UID. */
    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    /** Get the event summary. */
    public function getSummary(): ?string
    {
        return $this->getPropertyValue('SUMMARY');
    }

    /** Set the event summary. */
    public function setSummary(string $summary): void
    {
        $this->setProperty('SUMMARY', $summary);
    }

    /** Get the event description. */
    public function getDescription(): ?string
    {
        return $this->getPropertyValue('DESCRIPTION');
    }

    /** Set the event description. */
    public function setDescription(string $description): void
    {
        $this->setProperty('DESCRIPTION', $description);
    }

    /** Get the event location. */
    public function getLocation(): ?string
    {
        return $this->getPropertyValue('LOCATION');
    }

    /** Set the event location. */
    public function setLocation(string $location): void
    {
        $this->setProperty('LOCATION', $location);
    }

    /** Get the event URL. */
    public function getUrl(): ?string
    {
        return $this->getPropertyValue('URL');
    }

    /** Set the event URL. */
    public function setUrl(string $url): void
    {
        $this->setProperty('URL', $url);
    }

    /** @section Integer properties */

    /** Get the event revision sequence number. */
    public function getSequence(): int
    {
        $raw = $this->getPropertyValue('SEQUENCE');
        return $raw !== null ? (int) $raw : 0;
    }

    /** Set the event revision sequence number. */
    public function setSequence(int $sequence): void
    {
        $this->setProperty('SEQUENCE', (string) $sequence);
    }

    /** Get the event priority (0 = undefined, 1 = highest, 9 = lowest). */
    public function getPriority(): int
    {
        $raw = $this->getPropertyValue('PRIORITY');
        return $raw !== null ? (int) $raw : 0;
    }

    /** Set the event priority. */
    public function setPriority(int $priority): void
    {
        $this->setProperty('PRIORITY', (string) $priority);
    }

    /** @section Enum properties */

    /** Get the event status. */
    public function getStatus(): ?EventStatus
    {
        $raw = $this->getPropertyValue('STATUS');
        return $raw !== null ? EventStatus::from($raw) : null;
    }

    /** Set the event status. */
    public function setStatus(EventStatus $status): void
    {
        $this->setProperty('STATUS', $status->value);
    }

    /** Get the event access classification, defaults to PUBLIC. */
    public function getClassification(): Classification
    {
        $raw = $this->getPropertyValue('CLASS');
        return $raw !== null ? Classification::from($raw) : Classification::from('PUBLIC');
    }

    /** Set the event access classification. */
    public function setClassification(Classification $class): void
    {
        $this->setProperty('CLASS', $class->value);
    }

    /** Get the event time transparency, defaults to OPAQUE. */
    public function getTransparency(): Transparency
    {
        $raw = $this->getPropertyValue('TRANSP');
        return $raw !== null ? Transparency::from($raw) : Transparency::from('OPAQUE');
    }

    /** Set the event time transparency. */
    public function setTransparency(Transparency $transp): void
    {
        $this->setProperty('TRANSP', $transp->value);
    }

    /** @section Date/time properties */

    /** Get the event start date/time. */
    public function getDtstart(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTART');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    /** Set the event start date/time. */
    public function setDtstart(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTSTART', DateTimeParser::format($dt, $tzid), $params);
    }

    /** Get the event end date/time. */
    public function getDtend(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTEND');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    /** Set the event end date/time. */
    public function setDtend(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTEND', DateTimeParser::format($dt, $tzid), $params);
    }

    /** Get the event duration as an ISO 8601 duration string. */
    public function getDuration(): ?string
    {
        return $this->getPropertyValue('DURATION');
    }

    /** Set the event duration as an ISO 8601 duration string. */
    public function setDuration(string $duration): void
    {
        $this->setProperty('DURATION', $duration);
    }

    /** Get the date/time the event was created. */
    public function getCreated(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('CREATED');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    /** Get the date/time the event was last modified. */
    public function getLastModified(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('LAST-MODIFIED');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    /** Get the date/time stamp of the event. */
    public function getDtstamp(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTAMP');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue());
    }

    /** Set the date/time stamp of the event. */
    public function setDtstamp(DateTimeImmutable $dt): void
    {
        $this->setProperty('DTSTAMP', DateTimeParser::format($dt));
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

    /** Add an attendee to the event. */
    public function addAttendee(Attendee $attendee): void
    {
        $this->getProperties()->addProperty($attendee->getProperty());
    }

    /** Get the event organizer. */
    public function getOrganizer(): ?Organizer
    {
        $prop = $this->getProperties()->get('ORGANIZER');
        return $prop !== null ? new Organizer($prop) : null;
    }

    /** Set the event organizer, replacing any existing one. */
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

    /** @return list<string> */
    public function getExdateValues(): array
    {
        $values = $this->getProperties()->getAllValues('EXDATE');
        $dates = [];
        foreach ($values as $val) {
            foreach (explode(',', $val) as $dateStr) {
                $trimmed = trim($dateStr);
                if ($trimmed !== '') {
                    $dates[] = $trimmed;
                }
            }
        }
        return $dates;
    }

    /** @return list<string> */
    public function getRdateValues(): array
    {
        $values = $this->getProperties()->getAllValues('RDATE');
        $dates = [];
        foreach ($values as $val) {
            foreach (explode(',', $val) as $dateStr) {
                $trimmed = trim($dateStr);
                if ($trimmed !== '') {
                    $dates[] = $trimmed;
                }
            }
        }
        return $dates;
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

    /** Set the event categories. */
    public function setCategories(array $categories): void
    {
        $this->setProperty('CATEGORIES', implode(',', $categories));
    }
}
