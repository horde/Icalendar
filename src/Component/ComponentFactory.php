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

use Horde\Icalendar\Property\PropertyBag;

/**
 * Creates component instances by type string.
 *
 * Currently returns GenericComponent for all types. When typed
 * subclasses are introduced (Step 5), this factory will map known
 * types to their dedicated classes.
 */
final class ComponentFactory
{
    /**
     * Create a component by its type string (e.g. "VCALENDAR", "VEVENT").
     */
    public function create(string $type, ?PropertyBag $properties = null): AbstractComponent
    {
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
