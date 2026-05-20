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
