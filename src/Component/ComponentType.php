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

enum ComponentType: string
{
    case VCalendar = 'VCALENDAR';
    case Vevent = 'VEVENT';
    case Vtodo = 'VTODO';
    case Vjournal = 'VJOURNAL';
    case Vfreebusy = 'VFREEBUSY';
    case Vtimezone = 'VTIMEZONE';
    case Standard = 'STANDARD';
    case Daylight = 'DAYLIGHT';
    case Valarm = 'VALARM';
    case VCard = 'VCARD';
}
