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
 * Open enum for PARTSTAT (RFC 5545 Section 3.2.12).
 *
 * Stores the raw string value. Unknown/extension values (X-*, IANA tokens)
 * are preserved. The effective() method returns the RFC-mandated default
 * (NEEDS-ACTION) when the value is unknown.
 */
final readonly class ParticipationStatus
{
    public const NEEDS_ACTION = 'NEEDS-ACTION';
    public const ACCEPTED = 'ACCEPTED';
    public const DECLINED = 'DECLINED';
    public const TENTATIVE = 'TENTATIVE';
    public const DELEGATED = 'DELEGATED';
    public const COMPLETED = 'COMPLETED';
    public const IN_PROCESS = 'IN-PROCESS';

    private const KNOWN = [
        self::NEEDS_ACTION,
        self::ACCEPTED,
        self::DECLINED,
        self::TENTATIVE,
        self::DELEGATED,
        self::COMPLETED,
        self::IN_PROCESS,
    ];

    private const DEFAULT = self::NEEDS_ACTION;

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
