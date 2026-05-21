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
 * Immutable representation of a content-line parameter.
 *
 * @param string   $name   Parameter name (uppercase-normalized)
 * @param string[] $values Parameter values (always array; single value = 1-element)
 */
final readonly class Parameter
{
    /** @var string[] */
    public array $values;

    /**
     * @param string[] $values
     */
    public function __construct(
        public string $name,
        array $values,
    ) {
        $this->values = array_values($values);
    }

    /** Get the first (or only) parameter value. */
    public function getValue(): string
    {
        return $this->values[0] ?? '';
    }

    /** Whether this parameter has multiple comma-separated values. */
    public function isMultiValue(): bool
    {
        return count($this->values) > 1;
    }
}
