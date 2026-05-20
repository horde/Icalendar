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
 * Catch-all component for any type that does not yet have a dedicated class.
 *
 * Stores the component type as a string, returned by getType().
 * When typed subclasses (Vevent, Vtodo, etc.) are introduced, the
 * ComponentFactory will route to them instead of GenericComponent.
 */
final class GenericComponent extends AbstractComponent
{
    private readonly string $type;

    public function __construct(string $type, ?PropertyBag $properties = null)
    {
        $this->type = strtoupper($type);
        parent::__construct($properties);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
