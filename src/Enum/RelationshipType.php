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
 * Open enum for RELTYPE parameter (RFC 5545 Section 3.2.15).
 *
 * No default — absence means the property is not set.
 */
final readonly class RelationshipType
{
    public const PARENT = 'PARENT';
    public const CHILD = 'CHILD';
    public const SIBLING = 'SIBLING';

    private const KNOWN = [
        self::PARENT,
        self::CHILD,
        self::SIBLING,
    ];

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
        return $this->value;
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
