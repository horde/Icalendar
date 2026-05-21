<?php

declare(strict_types=1);

/**
 * Copyright 2003-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author    Ralf Lang <ralf.lang@ralf-lang.de>
 * @category  Horde
 * @copyright 2003-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Value;

use Horde\Icalendar\Property\Property;

/**
 * Typed view of a vCard N (structured name) property.
 *
 * The N property value is semicolon-delimited:
 * family;given;additional;prefix;suffix
 */
final class StructuredName
{
    public function __construct(
        private Property $property,
    ) {}

    /** Create a StructuredName from individual name components. */
    public static function create(
        string $family = '',
        string $given = '',
        string $additional = '',
        string $prefix = '',
        string $suffix = '',
    ): self {
        $value = implode(';', [$family, $given, $additional, $prefix, $suffix]);
        return new self(new Property('N', $value));
    }

    /** Get the family (last) name. */
    public function getFamily(): string
    {
        return $this->getPart(0);
    }

    /** Get the given (first) name. */
    public function getGiven(): string
    {
        return $this->getPart(1);
    }

    /** Get the additional (middle) name(s). */
    public function getAdditional(): string
    {
        return $this->getPart(2);
    }

    /** Get the honorific prefix (e.g. Mr., Dr.). */
    public function getPrefix(): string
    {
        return $this->getPart(3);
    }

    /** Get the honorific suffix (e.g. Jr., III). */
    public function getSuffix(): string
    {
        return $this->getPart(4);
    }

    /** Get the underlying Property instance. */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /** Get a semicolon-delimited part by index. */
    private function getPart(int $index): string
    {
        $parts = explode(';', $this->property->getValue());
        return $parts[$index] ?? '';
    }
}
