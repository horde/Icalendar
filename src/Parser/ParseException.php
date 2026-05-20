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
