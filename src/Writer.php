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

/**
 * Serializes a component tree to iCalendar/vCard text.
 */
class Writer
{
    public function writeString(RootComponent $root): string
    {
        return $root->toString();
    }

    /**
     * @param resource $stream
     */
    public function writeStream(RootComponent $root, $stream): void
    {
        fwrite($stream, $root->toString());
    }
}
