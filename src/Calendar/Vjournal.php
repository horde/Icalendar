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
use Horde\Icalendar\Enum\Classification;
use Horde\Icalendar\Enum\JournalStatus;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Value\DateTimeParser;

class Vjournal extends AbstractComponent
{
    /** Return the iCalendar component type identifier. */
    public function getType(): string
    {
        return 'VJOURNAL';
    }

    /** Get the UID property value. */
    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    /** Set the UID property value. */
    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    /** Get the journal entry summary. */
    public function getSummary(): ?string
    {
        return $this->getPropertyValue('SUMMARY');
    }

    /** Set the journal entry summary. */
    public function setSummary(string $summary): void
    {
        $this->setProperty('SUMMARY', $summary);
    }

    /** Get the journal entry description. */
    public function getDescription(): ?string
    {
        return $this->getPropertyValue('DESCRIPTION');
    }

    /** Set the journal entry description. */
    public function setDescription(string $description): void
    {
        $this->setProperty('DESCRIPTION', $description);
    }

    /** Get the journal entry status. */
    public function getStatus(): ?JournalStatus
    {
        $raw = $this->getPropertyValue('STATUS');
        return $raw !== null ? JournalStatus::from($raw) : null;
    }

    /** Set the journal entry status. */
    public function setStatus(JournalStatus $status): void
    {
        $this->setProperty('STATUS', $status->value);
    }

    /** Get the classification (public, private, confidential). */
    public function getClassification(): Classification
    {
        $raw = $this->getPropertyValue('CLASS');
        return $raw !== null ? Classification::from($raw) : Classification::from('PUBLIC');
    }

    /** Set the classification (public, private, confidential). */
    public function setClassification(Classification $class): void
    {
        $this->setProperty('CLASS', $class->value);
    }

    /** Get the start date/time of the journal entry. */
    public function getDtstart(): ?DateTimeImmutable
    {
        $prop = $this->getProperties()->get('DTSTART');
        if ($prop === null) {
            return null;
        }
        return DateTimeParser::parse($prop->getValue(), $prop->getParameter('TZID')?->getValue());
    }

    /** Set the start date/time of the journal entry. */
    public function setDtstart(DateTimeImmutable $dt, ?string $tzid = null): void
    {
        $params = [];
        if ($tzid !== null) {
            $params['TZID'] = new Parameter('TZID', [$tzid]);
        }
        $this->setProperty('DTSTART', DateTimeParser::format($dt, $tzid), $params);
    }

    /** @return list<string> */
    public function getCategories(): array
    {
        $raw = $this->getPropertyValue('CATEGORIES');
        if ($raw === null) {
            return [];
        }
        return array_map('trim', explode(',', $raw));
    }

    /** Set the categories for this journal entry. */
    public function setCategories(array $categories): void
    {
        $this->setProperty('CATEGORIES', implode(',', $categories));
    }
}
