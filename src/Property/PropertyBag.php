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

namespace Horde\Icalendar\Property;

use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\Parameter;

/**
 * Ordered collection of properties — the single source of truth for a component.
 *
 * Supports multi-occurrence properties (ATTENDEE, EXDATE, etc.) and
 * provides both single-get and multi-get query interfaces.
 */
final class PropertyBag
{
    /** @var Property[] */
    private array $properties = [];

    /**
     * Build a bag from parser output.
     *
     * @param iterable<ContentLine> $contentLines
     */
    public static function fromContentLines(iterable $contentLines): self
    {
        $bag = new self();

        foreach ($contentLines as $cl) {
            $bag->addProperty(Property::fromContentLine($cl));
        }

        return $bag;
    }

    /**
     * Get first occurrence of a property by name.
     */
    public function get(string $name): ?Property
    {
        $name = strtoupper($name);

        foreach ($this->properties as $prop) {
            if ($prop->getName() === $name) {
                return $prop;
            }
        }

        return null;
    }

    /**
     * Get the raw value of the first occurrence.
     */
    public function getValue(string $name): ?string
    {
        return $this->get($name)?->getValue();
    }

    /**
     * Get all occurrences of a property by name.
     *
     * @return Property[]
     */
    public function getAll(string $name): array
    {
        $name = strtoupper($name);
        $results = [];

        foreach ($this->properties as $prop) {
            if ($prop->getName() === $name) {
                $results[] = $prop;
            }
        }

        return $results;
    }

    /**
     * Get all raw values for a property name.
     *
     * @return string[]
     */
    public function getAllValues(string $name): array
    {
        return array_map(
            fn(Property $p) => $p->getValue(),
            $this->getAll($name),
        );
    }

    public function has(string $name): bool
    {
        return $this->get($name) !== null;
    }

    /**
     * Count occurrences of a property.
     */
    public function count(string $name): int
    {
        $name = strtoupper($name);
        $count = 0;

        foreach ($this->properties as $prop) {
            if ($prop->getName() === $name) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Set a property, replacing ALL existing occurrences of that name.
     *
     * @param array<string, Parameter> $parameters
     */
    public function set(
        string $name,
        string $value,
        array $parameters = [],
        ?string $group = null,
    ): void {
        $this->remove($name);
        $this->properties[] = new Property($name, $value, $parameters, $group);
    }

    /**
     * Add a new occurrence of a property (multi-value: ATTENDEE, EXDATE, etc.).
     *
     * @param array<string, Parameter> $parameters
     */
    public function add(
        string $name,
        string $value,
        array $parameters = [],
        ?string $group = null,
    ): void {
        $this->properties[] = new Property($name, $value, $parameters, $group);
    }

    /**
     * Add a pre-built Property object.
     */
    public function addProperty(Property $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * Remove ALL occurrences of a property by name.
     */
    public function remove(string $name): void
    {
        $name = strtoupper($name);
        $this->properties = array_values(array_filter(
            $this->properties,
            fn(Property $p) => $p->getName() !== $name,
        ));
    }

    /**
     * Remove a specific occurrence by name and index (0-based among that name's occurrences).
     */
    public function removeAt(string $name, int $index): void
    {
        $name = strtoupper($name);
        $seen = 0;

        foreach ($this->properties as $key => $prop) {
            if ($prop->getName() === $name) {
                if ($seen === $index) {
                    unset($this->properties[$key]);
                    $this->properties = array_values($this->properties);
                    return;
                }
                $seen++;
            }
        }
    }

    /**
     * Get all properties in insertion order.
     *
     * @return Property[]
     */
    public function all(): array
    {
        return $this->properties;
    }

    /**
     * Get unique property names present in the bag.
     *
     * @return string[]
     */
    public function names(): array
    {
        $names = [];

        foreach ($this->properties as $prop) {
            $names[$prop->getName()] = true;
        }

        return array_keys($names);
    }

    /**
     * Serialize all properties to content-line strings (without folding).
     *
     * @return string[]
     */
    public function toContentLines(): array
    {
        return array_map(
            fn(Property $p) => $p->toContentLine(),
            $this->properties,
        );
    }
}
