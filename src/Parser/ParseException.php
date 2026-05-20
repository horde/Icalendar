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

use Horde\Icalendar\IcalendarException;
use RuntimeException;
use Throwable;

final class ParseException extends RuntimeException implements IcalendarException
{
    public function __construct(
        string $message,
        public readonly int $lineNumber,
        public readonly int $byteOffset,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('%s at line %d (byte %d)', $message, $lineNumber, $byteOffset),
            0,
            $previous,
        );
    }
}
