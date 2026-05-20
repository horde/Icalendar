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

namespace Horde\Icalendar\Value;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Parses and formats iCalendar DATE and DATE-TIME values (RFC 5545 Section 3.3.4/3.3.5).
 *
 * Handles three forms:
 * - DATE: YYYYMMDD
 * - DATE-TIME local: YYYYMMDDTHHmmss
 * - DATE-TIME UTC: YYYYMMDDTHHmmssZ
 *
 * TZID resolution applies to local DATE-TIME values when a TZID parameter is provided.
 */
final class DateTimeParser
{
    /**
     * Parse an iCalendar date or date-time value.
     *
     * @param string $value The raw property value (e.g. "20260520T090000Z")
     * @param string|null $tzid Optional TZID parameter value (e.g. "America/New_York")
     * @return DateTimeImmutable
     * @throws InvalidArgumentException On unparseable input
     */
    public static function parse(string $value, ?string $tzid = null): DateTimeImmutable
    {
        $value = trim($value);

        if (self::isDate($value)) {
            $dt = DateTimeImmutable::createFromFormat('Ymd|', $value, new DateTimeZone('UTC'));
            if ($dt === false) {
                throw new InvalidArgumentException("Invalid DATE value: $value");
            }
            return $dt;
        }

        if (str_ends_with($value, 'Z')) {
            $dt = DateTimeImmutable::createFromFormat('Ymd\THis\Z', $value, new DateTimeZone('UTC'));
            if ($dt === false) {
                throw new InvalidArgumentException("Invalid DATE-TIME UTC value: $value");
            }
            return $dt;
        }

        $tz = $tzid !== null ? new DateTimeZone($tzid) : new DateTimeZone('UTC');
        $dt = DateTimeImmutable::createFromFormat('Ymd\THis', $value, $tz);
        if ($dt === false) {
            throw new InvalidArgumentException("Invalid DATE-TIME value: $value");
        }

        return $dt;
    }

    /**
     * Format a DateTimeImmutable to iCalendar string.
     *
     * @param DateTimeImmutable $dt The date-time to format
     * @param string|null $tzid If null and timezone is UTC, appends 'Z'. If non-null, formats as local time (TZID goes in parameter).
     * @param bool $dateOnly If true, format as DATE (YYYYMMDD) only
     * @return string The formatted value
     */
    public static function format(DateTimeImmutable $dt, ?string $tzid = null, bool $dateOnly = false): string
    {
        if ($dateOnly) {
            return $dt->format('Ymd');
        }

        if ($tzid !== null) {
            $dt = $dt->setTimezone(new DateTimeZone($tzid));
            return $dt->format('Ymd\THis');
        }

        if ($dt->getTimezone()->getName() === 'UTC') {
            return $dt->format('Ymd\THis\Z');
        }

        return $dt->format('Ymd\THis');
    }

    /**
     * Determine if a value is a DATE (no time component) rather than DATE-TIME.
     */
    public static function isDate(string $value): bool
    {
        return strlen($value) === 8 && ctype_digit($value);
    }
}
