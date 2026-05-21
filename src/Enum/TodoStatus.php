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
 * Open enum for VTODO STATUS property (RFC 5545 Section 3.8.1.11).
 *
 * No default — absence means the property is not set.
 */
final readonly class TodoStatus
{
    public const NEEDS_ACTION = 'NEEDS-ACTION';
    public const COMPLETED = 'COMPLETED';
    public const IN_PROCESS = 'IN-PROCESS';
    public const CANCELLED = 'CANCELLED';

    private const KNOWN = [
        self::NEEDS_ACTION,
        self::COMPLETED,
        self::IN_PROCESS,
        self::CANCELLED,
    ];

    /** Create instance wrapping the given value. */
    private function __construct(
        public string $value,
    ) {}

    /** Create an instance from a raw string value. */
    public static function from(string $value): self
    {
        return new self(strtoupper($value));
    }

    /** Check whether the value is one of the RFC-defined constants. */
    public function isKnown(): bool
    {
        return in_array($this->value, self::KNOWN, true);
    }

    /** Return the effective value, falling back to default if applicable. */
    public function effective(): string
    {
        return $this->value;
    }

    /** Return the raw string representation. */
    public function toString(): string
    {
        return $this->value;
    }

    /** Check equality by comparing underlying string values. */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
