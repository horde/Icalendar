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

namespace Horde\Icalendar;

use Generator;
use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Component\ComponentFactory;
use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\ContentLineParser;
use Horde\Icalendar\Property\Property;
use RuntimeException;

/**
 * Reads iCalendar/vCard data from strings, files, or streams.
 *
 * Provides both DOM-style (full parse) and streaming (generator-based)
 * access to component data.
 */
class Reader
{
    /**
     * @param resource $stream
     */
    public function readStream($stream): RootComponent
    {
        $content = stream_get_contents($stream);

        if ($content === false) {
            throw new RuntimeException('Failed to read stream');
        }

        $component = AbstractComponent::fromString($content);

        if (!$component instanceof RootComponent) {
            throw new RuntimeException('Stream does not contain a root component (VCALENDAR or VCARD)');
        }

        return $component;
    }

    public function readFile(string $filename): RootComponent
    {
        $content = file_get_contents($filename);

        if ($content === false) {
            throw new RuntimeException('Failed to read file: ' . $filename);
        }

        $component = AbstractComponent::fromString($content);

        if (!$component instanceof RootComponent) {
            throw new RuntimeException('File does not contain a root component (VCALENDAR or VCARD)');
        }

        return $component;
    }

    /**
     * Stream components from a resource, yielding each top-level child
     * component (VEVENT, VTODO, VJOURNAL, etc.) as it is fully parsed.
     *
     * Memory-efficient for large .ics files with many events.
     * The wrapper VCALENDAR/VCARD properties are available on the
     * returned root component via getRootProperties().
     *
     * @param resource $stream
     * @return Generator<int, Component>
     */
    public function streamComponents($stream, ?ComponentFactory $factory = null): Generator
    {
        $content = stream_get_contents($stream);
        if ($content === false) {
            throw new RuntimeException('Failed to read stream');
        }

        $factory ??= new ComponentFactory();
        $parser = new ContentLineParser();
        $lines = iterator_to_array($parser->parse($content));

        if ($lines === []) {
            return;
        }

        $index = 0;
        $count = count($lines);

        // Expect opening BEGIN:VCALENDAR or BEGIN:VCARD
        if ($index < $count && $lines[$index]->name === 'BEGIN') {
            $rootType = strtoupper($lines[$index]->value);
            $index++;
        } else {
            return;
        }

        // Parse content lines until END of root
        while ($index < $count) {
            $current = $lines[$index];

            if ($current->name === 'END' && strtoupper($current->value) === $rootType) {
                return;
            }

            if ($current->name === 'BEGIN') {
                [$child, $index] = $this->buildComponent($lines, $index, $factory);
                yield $child;
                continue;
            }

            // Skip root-level properties (VERSION, PRODID, etc.) in streaming mode
            $index++;
        }
    }

    /**
     * @param ContentLine[] $lines
     * @return array{Component, int}
     */
    private function buildComponent(array $lines, int $index, ComponentFactory $factory): array
    {
        $line = $lines[$index];
        $type = strtoupper($line->value);
        $component = $factory->create($type);
        $index++;

        while ($index < count($lines)) {
            $current = $lines[$index];

            if ($current->name === 'END' && strtoupper($current->value) === $type) {
                return [$component, $index + 1];
            }

            if ($current->name === 'BEGIN') {
                [$child, $index] = $this->buildComponent($lines, $index, $factory);
                $component->addChild($child);
                continue;
            }

            $component->getProperties()->addProperty(Property::fromContentLine($current));
            $index++;
        }

        throw new RuntimeException("Unterminated component: missing END:$type");
    }
}
