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

    public static function fromContentLine(ContentLine $cl): self
    {
        return new self(
            $cl->name,
            $cl->value,
            $cl->parameters,
            $cl->group,
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

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

    public function getParameter(string $name): ?Parameter
    {
        return $this->parameters[strtoupper($name)] ?? null;
    }

    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[strtoupper($name)]);
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function setParameter(string $name, Parameter $param): void
    {
        $this->parameters[strtoupper($name)] = $param;
    }

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

    private function needsQuoting(string $value): bool
    {
        return $value !== '' && strcspn($value, ':;,') !== strlen($value);
    }
}
