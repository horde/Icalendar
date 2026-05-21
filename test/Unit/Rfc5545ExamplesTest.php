<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit;

use Horde\Icalendar\Calendar\VCalendar;
use Horde\Icalendar\Calendar\Vevent;
use Horde\Icalendar\Calendar\Vfreebusy;
use Horde\Icalendar\Calendar\Vjournal;
use Horde\Icalendar\Calendar\Vtimezone;
use Horde\Icalendar\Calendar\Vtodo;
use Horde\Icalendar\Component\AbstractComponent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests parsing canonical iCalendar examples from RFC 5545 §4 and RFC 5546 §4.
 *
 * Each test feeds the exact RFC text into AbstractComponent::fromString()
 * and verifies the parsed object model.
 */
#[CoversClass(AbstractComponent::class)]
final class Rfc5545ExamplesTest extends TestCase
{
    /**
     * RFC 5545 §4 Example 1: Three-day conference.
     */
    #[Test]
    public function parseConferenceEvent(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//xyz Corp//NONSGML PDA Calendar Version 1.0//EN',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'DTSTAMP:19960704T120000Z',
            'UID:uid1@example.com',
            'ORGANIZER:mailto:jsmith@example.com',
            'DTSTART:19960918T143000Z',
            'DTEND:19960920T220000Z',
            'STATUS:CONFIRMED',
            'CATEGORIES:CONFERENCE',
            'SUMMARY:Networld+Interop Conference',
            'DESCRIPTION:Networld+Interop Conference and Exhibit\nAtlanta World Congress Center\nAtlanta\, Georgia',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertInstanceOf(VCalendar::class, $cal);
        $this->assertSame('2.0', $cal->getPropertyValue('VERSION'));
        $this->assertSame('-//xyz Corp//NONSGML PDA Calendar Version 1.0//EN', $cal->getPropertyValue('PRODID'));

        $events = $cal->getChildrenByType('VEVENT');
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertInstanceOf(Vevent::class, $event);
        $this->assertSame('uid1@example.com', $event->getPropertyValue('UID'));
        $this->assertSame('mailto:jsmith@example.com', $event->getPropertyValue('ORGANIZER'));
        $this->assertSame('19960918T143000Z', $event->getPropertyValue('DTSTART'));
        $this->assertSame('19960920T220000Z', $event->getPropertyValue('DTEND'));
        $this->assertSame('CONFIRMED', $event->getPropertyValue('STATUS'));
        $this->assertSame('CONFERENCE', $event->getPropertyValue('CATEGORIES'));
        $this->assertSame('Networld+Interop Conference', $event->getPropertyValue('SUMMARY'));
        $this->assertStringContainsString('Atlanta', $event->getPropertyValue('DESCRIPTION'));
    }

    /**
     * RFC 5545 §4 Example 2: Group meeting with VTIMEZONE (America/New_York).
     */
    #[Test]
    public function parseGroupMeetingWithTimezone(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//RDU Software//NONSGML HandCal//EN',
            'VERSION:2.0',
            'BEGIN:VTIMEZONE',
            'TZID:America/New_York',
            'BEGIN:STANDARD',
            'DTSTART:19981025T020000',
            'TZOFFSETFROM:-0400',
            'TZOFFSETTO:-0500',
            'TZNAME:EST',
            'END:STANDARD',
            'BEGIN:DAYLIGHT',
            'DTSTART:19990404T020000',
            'TZOFFSETFROM:-0500',
            'TZOFFSETTO:-0400',
            'TZNAME:EDT',
            'END:DAYLIGHT',
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'DTSTAMP:19980309T231000Z',
            'UID:guid-1.example.com',
            'ORGANIZER:mailto:mrbig@example.com',
            'ATTENDEE;RSVP=TRUE;ROLE=REQ-PARTICIPANT;CUTYPE=GROUP:mailto:employee-A@example.com',
            'DESCRIPTION:Project XYZ Review Meeting',
            'CATEGORIES:MEETING',
            'CLASS:PUBLIC',
            'CREATED:19980309T130000Z',
            'SUMMARY:XYZ Project Review',
            'DTSTART;TZID=America/New_York:19980312T083000',
            'DTEND;TZID=America/New_York:19980312T093000',
            'LOCATION:1CP Conference Room 4350',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertInstanceOf(VCalendar::class, $cal);

        $timezones = $cal->getChildrenByType('VTIMEZONE');
        $this->assertCount(1, $timezones);
        $tz = $timezones[0];
        $this->assertInstanceOf(Vtimezone::class, $tz);
        $this->assertSame('America/New_York', $tz->getPropertyValue('TZID'));

        $tzChildren = $tz->getChildren();
        $this->assertCount(2, $tzChildren);

        $events = $cal->getChildrenByType('VEVENT');
        $this->assertCount(1, $events);
        $event = $events[0];

        $this->assertSame('guid-1.example.com', $event->getPropertyValue('UID'));
        $this->assertSame('XYZ Project Review', $event->getPropertyValue('SUMMARY'));
        $this->assertSame('PUBLIC', $event->getPropertyValue('CLASS'));
        $this->assertSame('1CP Conference Room 4350', $event->getPropertyValue('LOCATION'));

        $dtstart = $event->getProperties()->get('DTSTART');
        $this->assertSame('19980312T083000', $dtstart->getValue());
        $this->assertSame('America/New_York', $dtstart->getParameter('TZID')->getValue());

        $attendee = $event->getProperties()->get('ATTENDEE');
        $this->assertSame('TRUE', $attendee->getParameter('RSVP')->getValue());
        $this->assertSame('REQ-PARTICIPANT', $attendee->getParameter('ROLE')->getValue());
        $this->assertSame('GROUP', $attendee->getParameter('CUTYPE')->getValue());
    }

    /**
     * RFC 5545 §4 Example 3: Meeting with METHOD and ATTACH.
     */
    #[Test]
    public function parseMeetingWithMethodAndAttachments(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'METHOD:xyz',
            'VERSION:2.0',
            'PRODID:-//ABC Corporation//NONSGML My Product//EN',
            'BEGIN:VEVENT',
            'DTSTAMP:19970324T120000Z',
            'SEQUENCE:0',
            'UID:uid3@example.com',
            'ORGANIZER:mailto:jdoe@example.com',
            'ATTENDEE;RSVP=TRUE:mailto:jsmith@example.com',
            'DTSTART:19970324T123000Z',
            'DTEND:19970324T210000Z',
            'CATEGORIES:MEETING,PROJECT',
            'CLASS:PUBLIC',
            'SUMMARY:Calendaring Interoperability Planning Meeting',
            'DESCRIPTION:Discuss how we can test c&s interoperability\\nusing iCalendar and other IETF standards.',
            'LOCATION:LDB Lobby',
            'ATTACH;FMTTYPE=application/postscript:ftp://example.com/pub/conf/bkgrnd.ps',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('xyz', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('uid3@example.com', $event->getPropertyValue('UID'));
        $this->assertSame('0', $event->getPropertyValue('SEQUENCE'));
        $this->assertSame('MEETING,PROJECT', $event->getPropertyValue('CATEGORIES'));
        $this->assertSame('LDB Lobby', $event->getPropertyValue('LOCATION'));

        $attach = $event->getProperties()->get('ATTACH');
        $this->assertSame('ftp://example.com/pub/conf/bkgrnd.ps', $attach->getValue());
        $this->assertSame('application/postscript', $attach->getParameter('FMTTYPE')->getValue());
    }

    /**
     * RFC 5545 §4 Example 4: VTODO with VALARM.
     */
    #[Test]
    public function parseTodoWithAlarm(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ABC Corporation//NONSGML My Product//EN',
            'BEGIN:VTODO',
            'DTSTAMP:19980130T134500Z',
            'SEQUENCE:2',
            'UID:uid4@example.com',
            'ORGANIZER:mailto:unclesam@example.com',
            'ATTENDEE;PARTSTAT=ACCEPTED:mailto:jqpublic@example.com',
            'DUE:19980415T000000',
            'STATUS:NEEDS-ACTION',
            'SUMMARY:Submit Income Taxes',
            'BEGIN:VALARM',
            'ACTION:AUDIO',
            'TRIGGER:19980403T120000Z',
            'ATTACH;FMTTYPE=audio/basic:http://example.com/pub/audio-files/ssbanner.aud',
            'REPEAT:4',
            'DURATION:PT1H',
            'END:VALARM',
            'END:VTODO',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $todos = $cal->getChildrenByType('VTODO');
        $this->assertCount(1, $todos);

        $todo = $todos[0];
        $this->assertInstanceOf(Vtodo::class, $todo);
        $this->assertSame('uid4@example.com', $todo->getPropertyValue('UID'));
        $this->assertSame('2', $todo->getPropertyValue('SEQUENCE'));
        $this->assertSame('19980415T000000', $todo->getPropertyValue('DUE'));
        $this->assertSame('NEEDS-ACTION', $todo->getPropertyValue('STATUS'));
        $this->assertSame('Submit Income Taxes', $todo->getPropertyValue('SUMMARY'));

        $alarms = $todo->getChildrenByType('VALARM');
        $this->assertCount(1, $alarms);
        $alarm = $alarms[0];
        $this->assertSame('AUDIO', $alarm->getPropertyValue('ACTION'));
        $this->assertSame('19980403T120000Z', $alarm->getPropertyValue('TRIGGER'));
        $this->assertSame('4', $alarm->getPropertyValue('REPEAT'));
        $this->assertSame('PT1H', $alarm->getPropertyValue('DURATION'));
    }

    /**
     * RFC 5545 §4 Example 5: VJOURNAL with multi-line description.
     */
    #[Test]
    public function parseJournalEntry(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ABC Corporation//NONSGML My Product//EN',
            'BEGIN:VJOURNAL',
            'DTSTAMP:19970324T120000Z',
            'UID:uid5@example.com',
            'ORGANIZER:mailto:jsmith@example.com',
            'STATUS:DRAFT',
            'CLASS:PUBLIC',
            'CATEGORIES:Project Report,XYZ,Weekly Meeting',
            'DESCRIPTION:Project xyz Review Meeting Minutes',
            'END:VJOURNAL',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $journals = $cal->getChildrenByType('VJOURNAL');
        $this->assertCount(1, $journals);

        $journal = $journals[0];
        $this->assertInstanceOf(Vjournal::class, $journal);
        $this->assertSame('uid5@example.com', $journal->getPropertyValue('UID'));
        $this->assertSame('DRAFT', $journal->getPropertyValue('STATUS'));
        $this->assertSame('PUBLIC', $journal->getPropertyValue('CLASS'));
        $this->assertStringContainsString('Project Report', $journal->getPropertyValue('CATEGORIES'));
    }

    /**
     * RFC 5545 §4 Example 6: Published VFREEBUSY.
     */
    #[Test]
    public function parseFreeBusy(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//RDU Software//NONSGML HandCal//EN',
            'BEGIN:VFREEBUSY',
            'ORGANIZER:mailto:jsmith@example.com',
            'DTSTART:19980313T141711Z',
            'DTEND:19980410T141711Z',
            'FREEBUSY:19980314T233000Z/19980315T003000Z',
            'FREEBUSY:19980316T153000Z/19980316T163000Z',
            'FREEBUSY:19980318T030000Z/19980318T040000Z',
            'URL:http://www.example.com/calendar/busytime/jsmith.ifb',
            'END:VFREEBUSY',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $freebusy = $cal->getChildrenByType('VFREEBUSY');
        $this->assertCount(1, $freebusy);

        $fb = $freebusy[0];
        $this->assertInstanceOf(Vfreebusy::class, $fb);
        $this->assertSame('mailto:jsmith@example.com', $fb->getPropertyValue('ORGANIZER'));
        $this->assertSame('19980313T141711Z', $fb->getPropertyValue('DTSTART'));
        $this->assertSame('19980410T141711Z', $fb->getPropertyValue('DTEND'));
        $this->assertSame('http://www.example.com/calendar/busytime/jsmith.ifb', $fb->getPropertyValue('URL'));

        $fbValues = $fb->getProperties()->getAll('FREEBUSY');
        $this->assertCount(3, $fbValues);
    }

    /**
     * RFC 5546 §4.1.1: Minimal PUBLISH event.
     */
    #[Test]
    public function parseMinimalPublish(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'VERSION:2.0',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'ORGANIZER:mailto:a@example.com',
            'DTSTAMP:19970610T172345Z',
            'DTSTART:19970714T170000Z',
            'DTEND:19970715T040000Z',
            'SUMMARY:Bastille Day Party',
            'UID:calsrv.example.com-873970198738777@example.com',
            'SEQUENCE:0',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('PUBLISH', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('Bastille Day Party', $event->getPropertyValue('SUMMARY'));
        $this->assertSame('calsrv.example.com-873970198738777@example.com', $event->getPropertyValue('UID'));
        $this->assertSame('0', $event->getPropertyValue('SEQUENCE'));
        $this->assertSame('CONFIRMED', $event->getPropertyValue('STATUS'));
    }

    /**
     * RFC 5546 §4.2.1: Group REQUEST with multiple attendees.
     */
    #[Test]
    public function parseGroupRequest(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'METHOD:REQUEST',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:a@example.com',
            'ATTENDEE;RSVP=TRUE;CUTYPE=INDIVIDUAL:mailto:b@example.com',
            'ATTENDEE;RSVP=TRUE;CUTYPE=INDIVIDUAL:mailto:c@example.com',
            'ATTENDEE;RSVP=TRUE;CUTYPE=INDIVIDUAL:mailto:d@example.com',
            'DTSTART:19970701T180000Z',
            'DTEND:19970701T190000Z',
            'SUMMARY:Phone Conference',
            'UID:calsrv.example.com-873970198738777@example.com',
            'SEQUENCE:0',
            'DTSTAMP:19970613T190000Z',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('REQUEST', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $attendees = $event->getProperties()->getAll('ATTENDEE');
        $this->assertCount(4, $attendees);

        $chair = $attendees[0];
        $this->assertSame('CHAIR', $chair->getParameter('ROLE')->getValue());
        $this->assertSame('ACCEPTED', $chair->getParameter('PARTSTAT')->getValue());

        $attendeeB = $attendees[1];
        $this->assertSame('TRUE', $attendeeB->getParameter('RSVP')->getValue());
        $this->assertSame('INDIVIDUAL', $attendeeB->getParameter('CUTYPE')->getValue());
    }

    /**
     * RFC 5546 §4.4.1: Recurring event with VTIMEZONE, RRULE, RDATE, EXDATE.
     */
    #[Test]
    public function parseRecurringEventWithTimezone(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'METHOD:REQUEST',
            'VERSION:2.0',
            'BEGIN:VTIMEZONE',
            'TZID:America-SanJose',
            'TZURL:http://example.com/tz/America-SanJose',
            'BEGIN:STANDARD',
            'DTSTART:19671029T020000',
            'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10',
            'TZOFFSETFROM:-0700',
            'TZOFFSETTO:-0800',
            'TZNAME:PST',
            'END:STANDARD',
            'BEGIN:DAYLIGHT',
            'DTSTART:19870405T020000',
            'RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4',
            'TZOFFSETFROM:-0800',
            'TZOFFSETTO:-0700',
            'TZNAME:PDT',
            'END:DAYLIGHT',
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED;CUTYPE=INDIVIDUAL:a@example.com',
            'ATTENDEE;RSVP=TRUE;CUTYPE=INDIVIDUAL:b@example.fr',
            'ATTENDEE;RSVP=TRUE;CUTYPE=INDIVIDUAL:c@example.jp',
            'DTSTAMP:19970613T190030Z',
            'DTSTART;TZID=America-SanJose:19970701T140000',
            'DTEND;TZID=America-SanJose:19970701T150000',
            'RRULE:FREQ=WEEKLY;COUNT=20;WKST=SU;BYDAY=TU',
            'RDATE;TZID=America-SanJose:19970910T140000',
            'EXDATE;TZID=America-SanJose:19970909T140000',
            'EXDATE;TZID=America-SanJose:19971028T140000',
            'SUMMARY:Weekly Phone Conference',
            'UID:calsrv.example.com-873970198738777@example.com',
            'SEQUENCE:0',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);

        $timezones = $cal->getChildrenByType('VTIMEZONE');
        $this->assertCount(1, $timezones);
        $this->assertSame('America-SanJose', $timezones[0]->getPropertyValue('TZID'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('Weekly Phone Conference', $event->getPropertyValue('SUMMARY'));
        $this->assertSame('FREQ=WEEKLY;COUNT=20;WKST=SU;BYDAY=TU', $event->getPropertyValue('RRULE'));

        $dtstart = $event->getProperties()->get('DTSTART');
        $this->assertSame('America-SanJose', $dtstart->getParameter('TZID')->getValue());

        $rdate = $event->getProperties()->get('RDATE');
        $this->assertNotNull($rdate);
        $this->assertSame('America-SanJose', $rdate->getParameter('TZID')->getValue());

        $exdates = $event->getProperties()->getAll('EXDATE');
        $this->assertCount(2, $exdates);
    }

    /**
     * RFC 5546 §4.4.2: Modified recurring instance with RECURRENCE-ID.
     */
    #[Test]
    public function parseModifiedInstance(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'METHOD:REQUEST',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:guid-1@example.com',
            'RECURRENCE-ID:19970701T210000Z',
            'SEQUENCE:1',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:a@example.com',
            'ATTENDEE:mailto:b@example.com',
            'ATTENDEE:mailto:c@example.com',
            'ATTENDEE:mailto:d@example.com',
            'DESCRIPTION:IETF-C&S Conference Call',
            'CLASS:PUBLIC',
            'SUMMARY:IETF Calendaring Working Group Meeting',
            'DTSTART:19970703T210000Z',
            'DTEND:19970703T220000Z',
            'LOCATION:Conference Call',
            'DTSTAMP:19970626T093000Z',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $event = $cal->getChildrenByType('VEVENT')[0];

        $this->assertSame('guid-1@example.com', $event->getPropertyValue('UID'));
        $this->assertSame('19970701T210000Z', $event->getPropertyValue('RECURRENCE-ID'));
        $this->assertSame('1', $event->getPropertyValue('SEQUENCE'));
        $this->assertSame('19970703T210000Z', $event->getPropertyValue('DTSTART'));
    }

    /**
     * RFC 5546 §4.4.3: Cancel a single instance.
     */
    #[Test]
    public function parseCancelledInstance(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'METHOD:CANCEL',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:guid-1@example.com',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:a@example.com',
            'ATTENDEE:mailto:b@example.com',
            'ATTENDEE:mailto:c@example.com',
            'ATTENDEE:mailto:d@example.com',
            'RECURRENCE-ID:19970801T210000Z',
            'SEQUENCE:2',
            'STATUS:CANCELLED',
            'DTSTAMP:19970721T093000Z',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('CANCEL', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('19970801T210000Z', $event->getPropertyValue('RECURRENCE-ID'));
        $this->assertSame('CANCELLED', $event->getPropertyValue('STATUS'));
        $this->assertSame('2', $event->getPropertyValue('SEQUENCE'));
    }

    /**
     * RFC 5546 §4.4.6: ADD a new instance to a recurring event.
     */
    #[Test]
    public function parseAddInstance(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'METHOD:ADD',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:123456789@example.com',
            'SEQUENCE:4',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:a@example.com',
            'ATTENDEE;RSVP=TRUE:mailto:b@example.com',
            'ATTENDEE;RSVP=TRUE:mailto:c@example.com',
            'ATTENDEE;RSVP=TRUE:mailto:d@example.com',
            'DESCRIPTION:IETF-C&S Conference Call',
            'CLASS:PUBLIC',
            'SUMMARY:IETF Calendaring Working Group Meeting',
            'DTSTART:19970715T210000Z',
            'DTEND:19970715T220000Z',
            'LOCATION:Conference Call',
            'DTSTAMP:19970629T093000Z',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('ADD', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('123456789@example.com', $event->getPropertyValue('UID'));
        $this->assertSame('4', $event->getPropertyValue('SEQUENCE'));
        $this->assertSame('19970715T210000Z', $event->getPropertyValue('DTSTART'));
    }

    /**
     * RFC 5546 §4.3.1: Publish busy time with multiple FREEBUSY periods.
     */
    #[Test]
    public function parseFreeBusyPublish(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'VERSION:2.0',
            'METHOD:PUBLISH',
            'BEGIN:VFREEBUSY',
            'DTSTAMP:19980101T124100Z',
            'ORGANIZER:mailto:a@example.com',
            'DTSTART:19980101T124200Z',
            'DTEND:19980108T124200Z',
            'FREEBUSY:19980101T180000Z/19980101T190000Z',
            'FREEBUSY:19980103T020000Z/19980103T050000Z',
            'FREEBUSY:19980107T020000Z/19980107T050000Z',
            'FREEBUSY:19980113T000000Z/19980113T010000Z',
            'FREEBUSY:19980115T190000Z/19980115T200000Z',
            'FREEBUSY:19980115T220000Z/19980115T230000Z',
            'FREEBUSY:19980116T013000Z/19980116T043000Z',
            'END:VFREEBUSY',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('PUBLISH', $cal->getPropertyValue('METHOD'));

        $fb = $cal->getChildrenByType('VFREEBUSY')[0];
        $this->assertInstanceOf(Vfreebusy::class, $fb);
        $this->assertSame('mailto:a@example.com', $fb->getPropertyValue('ORGANIZER'));

        $periods = $fb->getProperties()->getAll('FREEBUSY');
        $this->assertCount(7, $periods);
        $this->assertSame('19980101T180000Z/19980101T190000Z', $periods[0]->getValue());
    }

    /**
     * RFC 5546 §4.7.1: Minimal REFRESH request.
     */
    #[Test]
    public function parseRefreshRequest(): void
    {
        $input = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//Example/ExampleCalendarClient//EN',
            'METHOD:REFRESH',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'ORGANIZER:mailto:a@example.com',
            'ATTENDEE;ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:a@example.com',
            'ATTENDEE:mailto:b@example.com',
            'ATTENDEE:mailto:c@example.com',
            'ATTENDEE:mailto:d@example.com',
            'UID:guid-1-12345@example.com',
            'DTSTAMP:19970603T094000',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $cal = AbstractComponent::fromString($input);
        $this->assertSame('REFRESH', $cal->getPropertyValue('METHOD'));

        $event = $cal->getChildrenByType('VEVENT')[0];
        $this->assertSame('guid-1-12345@example.com', $event->getPropertyValue('UID'));
        $this->assertNull($event->getPropertyValue('DTSTART'));
        $this->assertNull($event->getPropertyValue('SUMMARY'));

        $attendees = $event->getProperties()->getAll('ATTENDEE');
        $this->assertCount(4, $attendees);
    }
}
