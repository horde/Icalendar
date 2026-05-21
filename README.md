# Horde iCalendar

`horde/icalendar` is a PHP library for parsing, manipulating, and writing iCalendar (RFC 5545) and vCard data. It provides both a low-level content-line parser and a typed object model with open enums, value wrappers, and component accessors.

## Installation

```bash
composer require horde/icalendar
```

Requires PHP 8.1+.

## Architecture

### Layers

| Layer | Namespace | Purpose |
|-------|-----------|---------|
| Parser/Writer | `Horde\Icalendar` | Content-line reader, streaming reader, writer |
| Components | `Horde\Icalendar\Component` | PropertyBag, AbstractComponent, ComponentFactory |
| Calendar types | `Horde\Icalendar\Calendar` | VCalendar, Vevent, Vtodo, Vjournal, Vfreebusy, Valarm, Vtimezone |
| Enums | `Horde\Icalendar\Enum` | Open enum classes (ParticipationStatus, CalendarMethod, EventStatus, etc.) |
| Values | `Horde\Icalendar\Value` | Attendee, Organizer, DateTimeParser |

### Boundaries

This library is responsible for:

- Parsing iCalendar/vCard byte streams into an object model
- Providing typed accessors for well known component properties (UID, DTSTART, ATTENDEE, etc.) and a generic bag for all parsed properties known and unknown.
- Serializing the object model back to RFC-compliant text
- Representing iCalendar values with correct semantics (open enums, date/time, attendee roles)

This library in its modern form is **not** responsible for:

- iTIP scheduling logic (REQUEST/REPLY/CANCEL processing) — see `horde/itip`
- Email transport (iMIP) will be handled by the upcoming `horde/imip` package
- CalDAV operations are handled by `horde/dav`
- Calendar and addressbook UI or storage. These are handled by application layers (Kronolith, Nag, Turba)

### Collaboration Partners

| Package | Relationship |
|---------|-------------|
| `horde/timezone` | Timezone handling, formatting and matching beyond IANA standards based builtins, ready to handle real world quirks (Outlook/Exchange, Apple) |
| `horde/date` | Recurrence calculations and date formatting |
| `horde/itip` | Consumes VCalendar, Vevent, Vtodo, Attendee, Organizer, and open enums for iTIP scheduling decisions |
| `horde/imip` (planned) | Will consume serialized VCalendar output for MIME email transport |
| `horde/dav` | Uses the parser/writer for CalDAV object exchange |
| Kronolith / Nag / Turba | Application layers that store and display calendar/task data |

## Upgrading

See [doc/UPGRADING.md](doc/UPGRADING.md) for migration guidance from `Horde_Icalendar` (PSR-0) to the modern `Horde\Icalendar` (PSR-4) API.

## Usage

```php
use Horde\Icalendar\Calendar\VCalendar;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Enum\CalendarMethod;
use Horde\Icalendar\Enum\ParticipationStatus;
use Horde\Icalendar\Value\Attendee;
use Horde\Icalendar\Value\Organizer;
use Horde\Icalendar\Reader;

// Parse from string
$reader = new Reader();
$cal = $reader->readString($icsData);

// Typed access
$event = $cal->getEvents()[0];
$uid = $event->getUid();
$start = $event->getDtstart();
$attendees = $event->getAttendees();

foreach ($attendees as $attendee) {
    $email = $attendee->getEmail();
    $status = $attendee->getParticipationStatus(); // ParticipationStatus open enum
}

// Build programmatically
$event = new Vevent();
$event->setUid('unique-id@example.com');
$event->setSummary('Team Meeting');
$event->setDtstart(new DateTimeImmutable('2026-06-01 10:00'), 'America/New_York');
$event->setOrganizer(Organizer::create('boss@example.com', 'The Boss'));
$event->addAttendee(Attendee::create('dev@example.com', 'Developer', ParticipationStatus::from('NEEDS-ACTION')));

$cal = new VCalendar();
$cal->setVersion();
$cal->setMethod(CalendarMethod::from('REQUEST'));
$cal->addChild($event);
```

## License

LGPL-2.1 — see [LICENSE](LICENSE).
