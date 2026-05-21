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

namespace Horde\Icalendar\Component;

use Horde\Icalendar\Component;
use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\ContentLineParser;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Parser\ParseException;
use Horde\Icalendar\Property\Property;
use Horde\Icalendar\Property\PropertyBag;

/**
 * Base class for all iCalendar/vCard components.
 *
 * Wraps a PropertyBag (single source of truth for properties) and
 * an ordered list of child components. Provides convenience methods
 * for property and child management.
 */
abstract class AbstractComponent implements Component
{
    private PropertyBag $properties;

    /** @var Component[] */
    private array $children = [];

    /** Create a component with an optional pre-populated property bag. */
    public function __construct(?PropertyBag $properties = null)
    {
        $this->properties = $properties ?? new PropertyBag();
    }

    /** Get the iCalendar component type identifier (e.g. VEVENT, VTODO). */
    abstract public function getType(): string;

    /** @section Component interface */

    /** Get the property bag for this component. */
    public function getProperties(): PropertyBag
    {
        return $this->properties;
    }

    /** @return Component[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** Add a child component. */
    public function addChild(Component $child): void
    {
        $this->children[] = $child;
    }

    /** @section Property convenience (delegates to bag) */

    /** Get a single property value by name. */
    public function getPropertyValue(string $name): ?string
    {
        return $this->properties->getValue($name);
    }

    /**
     * @param array<string, Parameter> $parameters
     */
    public function setProperty(string $name, string $value, array $parameters = []): void
    {
        $this->properties->set($name, $value, $parameters);
    }

    /**
     * @param array<string, Parameter> $parameters
     */
    public function addProperty(string $name, string $value, array $parameters = []): void
    {
        $this->properties->add($name, $value, $parameters);
    }

    /** Remove all properties with the given name. */
    public function removeProperty(string $name): void
    {
        $this->properties->remove($name);
    }

    /** @section Child component queries */

    /**
     * @return Component[]
     */
    public function getChildrenByType(string $type): array
    {
        $type = strtoupper($type);

        return array_values(array_filter(
            $this->children,
            fn(Component $c) => $c->getType() === $type,
        ));
    }

    /** Get the first child component matching the given type. */
    public function getFirstChild(string $type): ?Component
    {
        $type = strtoupper($type);

        foreach ($this->children as $child) {
            if ($child->getType() === $type) {
                return $child;
            }
        }

        return null;
    }

    /** Remove a specific child component by identity. */
    public function removeChild(Component $child): void
    {
        $this->children = array_values(array_filter(
            $this->children,
            fn(Component $c) => $c !== $child,
        ));
    }

    /** @section Serialization */

    /** Serialize this component and its children to an iCalendar string. */
    public function toString(): string
    {
        $output = 'BEGIN:' . $this->getType() . "\r\n";

        foreach ($this->properties->all() as $property) {
            $output .= self::fold($property->toContentLine()) . "\r\n";
        }

        foreach ($this->children as $child) {
            $output .= $child->toString();
        }

        $output .= 'END:' . $this->getType() . "\r\n";

        return $output;
    }

    /** Magic string conversion, delegates to toString(). */
    public function __toString(): string
    {
        return $this->toString();
    }

    /** @section Parsing */

    /**
     * Parse an iCalendar/vCard string into a component tree.
     */
    public static function fromString(string $input, ?ComponentFactory $factory = null): Component
    {
        $factory ??= new ComponentFactory();
        $parser = new ContentLineParser();
        $lines = iterator_to_array($parser->parse($input));

        if ($lines === []) {
            throw new ParseException('Empty input', 1, 0);
        }

        [$component, ] = self::buildComponent($lines, 0, $factory);

        return $component;
    }

    /**
     * Recursive component builder from a flat array of ContentLines.
     *
     * @param ContentLine[] $lines
     * @return array{Component, int} [component, nextIndex]
     */
    private static function buildComponent(array $lines, int $index, ComponentFactory $factory): array
    {
        $line = $lines[$index];

        if ($line->name !== 'BEGIN') {
            throw new ParseException(
                'Expected BEGIN, got ' . $line->name,
                $line->lineNumber,
                $line->byteOffset,
            );
        }

        $type = strtoupper($line->value);
        $component = $factory->create($type);
        $index++;

        while ($index < count($lines)) {
            $current = $lines[$index];

            if ($current->name === 'END' && strtoupper($current->value) === $type) {
                return [$component, $index + 1];
            }

            if ($current->name === 'BEGIN') {
                [$child, $index] = self::buildComponent($lines, $index, $factory);
                $component->addChild($child);
                continue;
            }

            $component->getProperties()->addProperty(Property::fromContentLine($current));
            $index++;
        }

        throw new ParseException(
            "Unterminated component: missing END:$type",
            $line->lineNumber,
            $line->byteOffset,
        );
    }

    /** @section Line folding */

    /**
     * Fold a content line at 75 octets per RFC 5545 §3.1.
     */
    private static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $result = '';
        $pos = 0;
        $len = strlen($line);
        $first = true;

        while ($pos < $len) {
            $chunkSize = $first ? 75 : 74;
            $chunk = substr($line, $pos, $chunkSize);

            if (!$first) {
                $result .= "\r\n ";
            }

            $result .= $chunk;
            $pos += $chunkSize;
            $first = false;
        }

        return $result;
    }
}
