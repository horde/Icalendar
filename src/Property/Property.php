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

namespace Horde\Icalendar\Property;

use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\Parameter;

/**
 * A single mutable property occurrence.
 *
 * Stores raw string value and parameters. Value interpretation (date
 * parsing, duration decoding) is NOT performed here — that's the job
 * of typed accessors on component classes.
 */
final class Property
{
    private string $name;
    private ?string $group;
    private string $value;
    /** @var array<string, Parameter> */
    private array $parameters;

    /**
     * @param array<string, Parameter> $parameters Keyed by uppercase name
     */
    public function __construct(
        string $name,
        string $value,
        array $parameters = [],
        ?string $group = null,
    ) {
        $this->name = strtoupper($name);
        $this->value = $value;
        $this->parameters = $parameters;
        $this->group = $group;
    }

    /** Create a Property from a parsed ContentLine. */
    public static function fromContentLine(ContentLine $cl): self
    {
        return new self(
            $cl->name,
            $cl->value,
            $cl->parameters,
            $cl->group,
        );
    }

    /** Get the property name (uppercase). */
    public function getName(): string
    {
        return $this->name;
    }

    /** Get the vCard group prefix, or null if absent. */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /** Get the raw property value string. */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /** Get a parameter by name, or null if not present. */
    public function getParameter(string $name): ?Parameter
    {
        return $this->parameters[strtoupper($name)] ?? null;
    }

    /** Check whether a parameter exists by name. */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[strtoupper($name)]);
    }

    /** Set the property value. */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /** Set or replace a parameter by name. */
    public function setParameter(string $name, Parameter $param): void
    {
        $this->parameters[strtoupper($name)] = $param;
    }

    /** Remove a parameter by name. */
    public function removeParameter(string $name): void
    {
        unset($this->parameters[strtoupper($name)]);
    }

    /**
     * Serialize to iCalendar content-line string (without folding).
     */
    public function toContentLine(): string
    {
        $line = '';

        if ($this->group !== null) {
            $line .= $this->group . '.';
        }

        $line .= $this->name;

        foreach ($this->parameters as $param) {
            $line .= ';' . $param->name;
            if ($param->values !== ['']) {
                $line .= '=' . implode(',', array_map(
                    fn(string $v) => $this->needsQuoting($v) ? '"' . $v . '"' : $v,
                    $param->values,
                ));
            }
        }

        $line .= ':' . $this->value;

        return $line;
    }

    /** Determine whether a parameter value requires DQUOTE quoting. */
    private function needsQuoting(string $value): bool
    {
        return $value !== '' && strcspn($value, ':;,') !== strlen($value);
    }
}
