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

use Horde\Icalendar\Enum\AttendeeRole;
use Horde\Icalendar\Enum\CuType;
use Horde\Icalendar\Enum\ParticipationStatus;
use Horde\Icalendar\Enum\ScheduleAgent;
use Horde\Icalendar\Parser\Parameter;
use Horde\Icalendar\Property\Property;

/**
 * Typed view of an ATTENDEE property.
 *
 * Wraps a Property instance, providing domain-typed access to the
 * mailto: URI value and its metadata parameters. Mutations update the
 * underlying Property in the bag.
 */
final class Attendee
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
        ?ParticipationStatus $partstat = null,
        ?AttendeeRole $role = null,
        ?bool $rsvp = null,
        ?ScheduleAgent $scheduleAgent = null,
        ?CuType $cutype = null,
        array $delegatedTo = [],
        array $delegatedFrom = [],
    ): self {
        $params = [];

        if ($cn !== null) {
            $params['CN'] = new Parameter('CN', [$cn]);
        }
        if ($partstat !== null) {
            $params['PARTSTAT'] = new Parameter('PARTSTAT', [$partstat->value]);
        }
        if ($role !== null) {
            $params['ROLE'] = new Parameter('ROLE', [$role->value]);
        }
        if ($rsvp !== null) {
            $params['RSVP'] = new Parameter('RSVP', [$rsvp ? 'TRUE' : 'FALSE']);
        }
        if ($scheduleAgent !== null) {
            $params['SCHEDULE-AGENT'] = new Parameter('SCHEDULE-AGENT', [$scheduleAgent->value]);
        }
        if ($cutype !== null) {
            $params['CUTYPE'] = new Parameter('CUTYPE', [$cutype->value]);
        }
        if ($delegatedTo !== []) {
            $params['DELEGATED-TO'] = new Parameter(
                'DELEGATED-TO',
                array_map(fn(string $e) => 'mailto:' . $e, $delegatedTo),
            );
        }
        if ($delegatedFrom !== []) {
            $params['DELEGATED-FROM'] = new Parameter(
                'DELEGATED-FROM',
                array_map(fn(string $e) => 'mailto:' . $e, $delegatedFrom),
            );
        }

        return new self(new Property('ATTENDEE', 'mailto:' . $email, $params));
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

    public function getParticipationStatus(): ParticipationStatus
    {
        $raw = $this->property->getParameter('PARTSTAT')?->getValue();
        return ($raw !== null && $raw !== '')
            ? ParticipationStatus::from($raw)
            : ParticipationStatus::from('NEEDS-ACTION');
    }

    public function getRole(): AttendeeRole
    {
        $raw = $this->property->getParameter('ROLE')?->getValue();
        return ($raw !== null && $raw !== '')
            ? AttendeeRole::from($raw)
            : AttendeeRole::from('REQ-PARTICIPANT');
    }

    public function getRsvp(): bool
    {
        $raw = $this->property->getParameter('RSVP')?->getValue();
        return strtoupper($raw ?? '') === 'TRUE';
    }

    public function getScheduleAgent(): ScheduleAgent
    {
        $raw = $this->property->getParameter('SCHEDULE-AGENT')?->getValue();
        return ($raw !== null && $raw !== '')
            ? ScheduleAgent::from($raw)
            : ScheduleAgent::from('SERVER');
    }

    public function getCuType(): CuType
    {
        $raw = $this->property->getParameter('CUTYPE')?->getValue();
        return ($raw !== null && $raw !== '')
            ? CuType::from($raw)
            : CuType::from('INDIVIDUAL');
    }

    /** @return string[] */
    public function getDelegatedTo(): array
    {
        $param = $this->property->getParameter('DELEGATED-TO');
        if ($param === null) {
            return [];
        }
        return array_map(
            fn(string $v) => str_starts_with(strtolower($v), 'mailto:') ? substr($v, 7) : $v,
            $param->values,
        );
    }

    /** @return string[] */
    public function getDelegatedFrom(): array
    {
        $param = $this->property->getParameter('DELEGATED-FROM');
        if ($param === null) {
            return [];
        }
        return array_map(
            fn(string $v) => str_starts_with(strtolower($v), 'mailto:') ? substr($v, 7) : $v,
            $param->values,
        );
    }

    public function setParticipationStatus(ParticipationStatus $status): void
    {
        $this->property->setParameter('PARTSTAT', new Parameter('PARTSTAT', [$status->value]));
    }

    public function setRole(AttendeeRole $role): void
    {
        $this->property->setParameter('ROLE', new Parameter('ROLE', [$role->value]));
    }

    public function setRsvp(bool $rsvp): void
    {
        if ($rsvp) {
            $this->property->setParameter('RSVP', new Parameter('RSVP', ['TRUE']));
        } else {
            $this->property->removeParameter('RSVP');
        }
    }

    public function setCommonName(string $cn): void
    {
        $this->property->setParameter('CN', new Parameter('CN', [$cn]));
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
