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
 * Typed view of a vCard ORG property.
 *
 * The ORG property value is semicolon-delimited:
 * organization;department;sub-department;...
 */
final class Organization
{
    public function __construct(
        private Property $property,
    ) {}

    /** Create an Organization from one or more hierarchical parts. */
    public static function create(string ...$parts): self
    {
        $value = implode(';', $parts);
        return new self(new Property('ORG', $value));
    }

    /** Get the top-level organization name. */
    public function getOrganization(): string
    {
        return $this->getPart(0);
    }

    /** Get the department (second hierarchical level). */
    public function getDepartment(): string
    {
        return $this->getPart(1);
    }

    /**
     * @return list<string>  All hierarchical parts (company, dept, sub-dept, ...)
     */
    public function getParts(): array
    {
        return explode(';', $this->property->getValue());
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
