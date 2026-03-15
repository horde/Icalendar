<?php

namespace Horde\Icalendar;

use BadMethodCallException;

/**
 * Read a vcalendar or vcard from file
 */
class Writer
{
    public function writeString(RootComponent $root): string
    {
        // TODO: Implement iCalendar serialization
        throw new BadMethodCallException('Writer::writeString() not yet implemented');
    }
}
