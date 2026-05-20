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

namespace Horde\Icalendar\Enum;

/**
 * Open enum for CUTYPE parameter (RFC 5545 Section 3.2.3).
 *
 * Default per RFC: INDIVIDUAL.
 */
final readonly class CuType
{
    public const INDIVIDUAL = 'INDIVIDUAL';
    public const GROUP = 'GROUP';
    public const RESOURCE = 'RESOURCE';
    public const ROOM = 'ROOM';
    public const UNKNOWN = 'UNKNOWN';

    private const KNOWN = [
        self::INDIVIDUAL,
        self::GROUP,
        self::RESOURCE,
        self::ROOM,
        self::UNKNOWN,
    ];

    private const DEFAULT = self::INDIVIDUAL;

    private function __construct(
        public string $value,
    ) {}

    public static function from(string $value): self
    {
        return new self(strtoupper($value));
    }

    public function isKnown(): bool
    {
        return in_array($this->value, self::KNOWN, true);
    }

    public function effective(): string
    {
        return $this->isKnown() ? $this->value : self::DEFAULT;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
