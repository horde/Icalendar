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

namespace Horde\Icalendar\Value;

use Horde\Icalendar\Enum\ScheduleAgent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;

/**
 * Typed view of an ORGANIZER property.
 *
 * Wraps a Property instance, providing domain-typed access to the
 * mailto: URI value and its metadata parameters.
 */
final class Organizer
{
    public function __construct(
        private Property $property,
    ) {}

    /**
     * Create from raw email + parameters for programmatic construction.
     */
    public static function create(
        string $email,
        ?string $cn = null,
        ?string $sentBy = null,
        ?ScheduleAgent $scheduleAgent = null,
    ): self {
        $params = [];

        if ($cn !== null) {
            $params['CN'] = new Parameter('CN', [$cn]);
        }
        if ($sentBy !== null) {
            $params['SENT-BY'] = new Parameter('SENT-BY', ['mailto:' . $sentBy]);
        }
        if ($scheduleAgent !== null) {
            $params['SCHEDULE-AGENT'] = new Parameter('SCHEDULE-AGENT', [$scheduleAgent->value]);
        }

        return new self(new Property('ORGANIZER', 'mailto:' . $email, $params));
    }

    public function getEmail(): string
    {
        $val = $this->property->getValue();
        if (str_starts_with(strtolower($val), 'mailto:')) {
            return substr($val, 7);
        }
        return $val;
    }

    public function getCommonName(): ?string
    {
        $val = $this->property->getParameter('CN')?->getValue();
        return ($val !== null && $val !== '') ? $val : null;
    }

    public function getSentBy(): ?string
    {
        $val = $this->property->getParameter('SENT-BY')?->getValue();
        if ($val === null || $val === '') {
            return null;
        }
        if (str_starts_with(strtolower($val), 'mailto:')) {
            return substr($val, 7);
        }
        return $val;
    }

    public function getScheduleAgent(): ScheduleAgent
    {
        $raw = $this->property->getParameter('SCHEDULE-AGENT')?->getValue();
        return ($raw !== null && $raw !== '')
            ? ScheduleAgent::from($raw)
            : ScheduleAgent::from('SERVER');
    }

    public function setCommonName(string $cn): void
    {
        $this->property->setParameter('CN', new Parameter('CN', [$cn]));
    }

    public function setSentBy(string $email): void
    {
        $this->property->setParameter('SENT-BY', new Parameter('SENT-BY', ['mailto:' . $email]));
    }

    public function setScheduleAgent(ScheduleAgent $agent): void
    {
        $this->property->setParameter('SCHEDULE-AGENT', new Parameter('SCHEDULE-AGENT', [$agent->value]));
    }

    public function getProperty(): Property
    {
        return $this->property;
    }
}
