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

namespace Horde\Icalendar\Recurrence;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use IteratorAggregate;
use Traversable;

/**
 * A bounded, sorted set of occurrence dates from recurrence expansion.
 *
 * @implements IteratorAggregate<int, DateTimeImmutable>
 */
final readonly class OccurrenceSet implements Countable, IteratorAggregate
{
    /** @var list<DateTimeImmutable> */
    private array $dates;

    /**
     * @param list<DateTimeImmutable> $dates Occurrence dates (will be sorted)
     */
    public function __construct(array $dates)
    {
        usort($dates, fn(DateTimeImmutable $a, DateTimeImmutable $b) => $a <=> $b);
        $this->dates = $dates;
    }

    public function count(): int
    {
        return count($this->dates);
    }

    public function isEmpty(): bool
    {
        return $this->dates === [];
    }

    public function first(): ?DateTimeImmutable
    {
        return $this->dates[0] ?? null;
    }

    public function last(): ?DateTimeImmutable
    {
        if ($this->dates === []) {
            return null;
        }

        return $this->dates[count($this->dates) - 1];
    }

    public function contains(DateTimeImmutable $date): bool
    {
        $target = $date->format('Y-m-d H:i:s');

        foreach ($this->dates as $d) {
            if ($d->format('Y-m-d H:i:s') === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<DateTimeImmutable>
     */
    public function toArray(): array
    {
        return $this->dates;
    }

    /**
     * @return Traversable<int, DateTimeImmutable>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->dates);
    }
}
