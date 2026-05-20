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

use Horde\Icalendar\Calendar\Daylight;
use Horde\Icalendar\Calendar\Standard;
use Horde\Icalendar\Calendar\Valarm;
use Horde\Icalendar\Calendar\VCalendar;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Calendar\Vfreebusy;
use Horde\Icalendar\Calendar\Vjournal;
use Horde\Icalendar\Calendar\Vtimezone;
use Horde\Icalendar\Calendar\Vtodo;
use Horde\Icalendar\Contact\VCard;
use Horde\Icalendar\Property\PropertyBag;

/**
 * Creates component instances by type string.
 *
 * Maps known types to their dedicated classes; falls back to
 * GenericComponent for unknown or extension types.
 */
final class ComponentFactory
{
    /** @var array<string, class-string<AbstractComponent>> */
    private const TYPE_MAP = [
        'VCALENDAR' => VCalendar::class,
        'VEVENT' => Vevent::class,
        'VTODO' => Vtodo::class,
        'VJOURNAL' => Vjournal::class,
        'VFREEBUSY' => Vfreebusy::class,
        'VTIMEZONE' => Vtimezone::class,
        'STANDARD' => Standard::class,
        'DAYLIGHT' => Daylight::class,
        'VALARM' => Valarm::class,
        'VCARD' => VCard::class,
    ];

    /**
     * Create a component by its type string (e.g. "VCALENDAR", "VEVENT").
     */
    public function create(string $type, ?PropertyBag $properties = null): AbstractComponent
    {
        $upper = strtoupper($type);
        $class = self::TYPE_MAP[$upper] ?? null;

        if ($class !== null) {
            return new $class($properties);
        }

        return new GenericComponent($type, $properties);
    }

    /**
     * Create a component from a ComponentType enum value.
     */
    public function createFromType(ComponentType $type, ?PropertyBag $properties = null): AbstractComponent
    {
        return $this->create($type->value, $properties);
    }
}
