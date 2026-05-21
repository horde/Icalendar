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
 * Typed view of a vCard ADR (structured address) property.
 *
 * The ADR property value is semicolon-delimited:
 * pobox;extended;street;locality;region;postalcode;country
 */
final class StructuredAddress
{
    public function __construct(
        private Property $property,
    ) {}

    /** Create a StructuredAddress from individual address components. */
    public static function create(
        string $pobox = '',
        string $extended = '',
        string $street = '',
        string $locality = '',
        string $region = '',
        string $postalcode = '',
        string $country = '',
    ): self {
        $value = implode(';', [$pobox, $extended, $street, $locality, $region, $postalcode, $country]);
        return new self(new Property('ADR', $value));
    }

    /** Get the post office box. */
    public function getPobox(): string
    {
        return $this->getPart(0);
    }

    /** Get the extended address (e.g. apartment or suite number). */
    public function getExtended(): string
    {
        return $this->getPart(1);
    }

    /** Get the street address. */
    public function getStreet(): string
    {
        return $this->getPart(2);
    }

    /** Get the locality (city). */
    public function getLocality(): string
    {
        return $this->getPart(3);
    }

    /** Get the region (state or province). */
    public function getRegion(): string
    {
        return $this->getPart(4);
    }

    /** Get the postal code. */
    public function getPostalcode(): string
    {
        return $this->getPart(5);
    }

    /** Get the country name. */
    public function getCountry(): string
    {
        return $this->getPart(6);
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
