<?php

declare(strict_types=1);

/**
 * Copyright 2003-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author    Mike Cochrane <mike@graftonhall.co.nz>
 * @author    Jan Schneider <jan@horde.org>
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Ralf Lang <ralf.lang@ralf-lang.de>
 * @category  Horde
 * @copyright 2003-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Calendar;

use Horde\Icalendar\Component;
use Horde\Icalendar\Component\AbstractComponent;

class Vtimezone extends AbstractComponent
{
    /** Return the iCalendar component type identifier. */
    public function getType(): string
    {
        return 'VTIMEZONE';
    }

    /** Get the STANDARD sub-component defining standard time rules. */
    public function getStandard(): ?Component
    {
        return $this->getFirstChild('STANDARD');
    }

    /** Get the DAYLIGHT sub-component defining daylight saving time rules. */
    public function getDaylight(): ?Component
    {
        return $this->getFirstChild('DAYLIGHT');
    }
}
