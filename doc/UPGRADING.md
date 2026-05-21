# Upgrading horde/icalendar

## From Horde_Icalendar (lib/) to Horde\Icalendar (src/)

The modern PSR-4 layer (`Horde\Icalendar\`) coexists with the legacy PSR-0 layer (`Horde_Icalendar` in `lib/`). Migration is opt-in. The legacy API remains functional.

### Namespace Change

| Legacy | Modern |
|--------|--------|
| `Horde_Icalendar` | `Horde\Icalendar\Calendar\VCalendar` |
| `Horde_Icalendar_Vevent` | `Horde\Icalendar\Calendar\Vevent` |
| `Horde_Icalendar_Vtodo` | `Horde\Icalendar\Calendar\Vtodo` |
| `Horde_Icalendar_Vfreebusy` | `Horde\Icalendar\Calendar\Vfreebusy` |

### Key Differences

#### Typed Accessors

Legacy code uses generic `getAttribute()`:

```php
$uid = $vevent->getAttribute('UID');
$dtstart = $vevent->getAttribute('DTSTART');
```

Modern code uses typed methods:

```php
$uid = $vevent->getUid();           // ?string
$dtstart = $vevent->getDtstart();   // ?DateTimeImmutable
$sequence = $vevent->getSequence(); // int (default 0)
```

#### Open Enums

Legacy code uses bare strings:

```php
$status = $vevent->getAttribute('STATUS'); // "CONFIRMED"
```

Modern code uses open enum objects:

```php
$status = $vevent->getStatus(); // ?EventStatus
$status->value;                 // "CONFIRMED"
$status->isKnown();            // true
$status->equals(EventStatus::from('CONFIRMED')); // true
```

Open enums accept any string value (including X-* extensions) without throwing. Use `isKnown()` to check if a value is RFC-defined.

#### Attendee/Organizer Wrappers

Legacy:

```php
$params = $vevent->getAttribute('ATTENDEE', true);
$email = $vevent->getAttribute('ATTENDEE');
```

Modern:

```php
$attendees = $vevent->getAttendees(); // list<Attendee>
$attendees[0]->getEmail();
$attendees[0]->getParticipationStatus(); // ParticipationStatus
$attendees[0]->getRole();               // AttendeeRole
$attendees[0]->setParticipationStatus(ParticipationStatus::from('ACCEPTED'));
```

#### Programmatic Construction

Legacy:

```php
$vevent = Horde_Icalendar::newComponent('VEVENT', $cal);
$vevent->setAttribute('SUMMARY', 'Meeting');
$vevent->setAttribute('ATTENDEE', 'mailto:user@example.com', ['PARTSTAT' => 'NEEDS-ACTION']);
```

Modern:

```php
$vevent = new Vevent();
$vevent->setSummary('Meeting');
$vevent->addAttendee(Attendee::create('user@example.com', null, ParticipationStatus::from('NEEDS-ACTION')));
```

#### Reader/Writer

Legacy:

```php
$ical = new Horde_Icalendar();
$ical->parsevCalendar($data);
$output = $ical->exportvCalendar();
```

Modern:

```php
$reader = new Reader();
$cal = $reader->readString($data);
// Streaming for large files:
foreach ($reader->streamComponents($stream) as $component) { ... }

$writer = new Writer();
$output = $writer->writeString($cal);
```

### Migration Strategy

1. New code should use the modern API exclusively
2. Existing integrations can migrate incrementally. Both layers read from/write to the same underlying PropertyBag
3. The `horde/itip` package is also dual in the same way.

### Compatibility with horde/itip

The `horde/itip` engine operates entirely on modern types:

- `ItipMessage` wraps a `VCalendar` with `CalendarMethod`
- `ItipProcessor` reads `Vevent` properties via typed accessors
- Generators produce `VCalendar` instances with proper METHOD and component structure

The `horde/itip` library ships with its legacy lib/ variant as a counterpart to horde/icalendar's legacy lib/ variant.

