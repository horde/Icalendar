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

namespace Horde\Icalendar\Parser;

/**
 * Immutable representation of a parsed iCalendar/vCard content line.
 *
 * Holds the raw structural components: group prefix, property name,
 * parameters, and value string. Value interpretation (date parsing,
 * duration decoding, etc.) is NOT performed here — that's the job of
 * higher layers.
 */
final readonly class ContentLine
{
    /**
     * @param string|null              $group      vCard group prefix (e.g. "item1"), null if absent
     * @param string                   $name       Property name (uppercase-normalized)
     * @param array<string, Parameter> $parameters Keyed by uppercase parameter name
     * @param string                   $value      Raw value string (not decoded)
     * @param int                      $lineNumber 1-based line number in original input
     * @param int                      $byteOffset Byte offset in original input
     * @param ParseError[]             $errors     Recoverable parse errors encountered
     */
    public function __construct(
        public ?string $group,
        public string $name,
        public array $parameters,
        public string $value,
        public int $lineNumber,
        public int $byteOffset,
        public array $errors = [],
    ) {}

    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[strtoupper($name)]);
    }

    public function getParameter(string $name): ?Parameter
    {
        return $this->parameters[strtoupper($name)] ?? null;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
