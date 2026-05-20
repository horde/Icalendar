<?php

declare(strict_types=1);

/**
 * Copyright 2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2026 The Horde Project
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

    public function getValue(): string
    {
        return $this->values[0] ?? '';
    }

    public function isMultiValue(): bool
    {
        return count($this->values) > 1;
    }
}
