<?php

/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 */

namespace Horde\Icalendar;

use Horde_Test_Case;
use Horde_Icalendar;
use Horde_Date;

/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 * @coversNothing
 */
class ParseTest extends Horde_Test_Case
{
    public function testEmptyData()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/empty.ics'));
        $this->assertEquals(
            [],
            $ical->getComponents()
        );
        $ical->parsevCalendar('');
        $this->assertEquals(
            [],
            $ical->getComponents()
        );
    }

    public function testEscapes()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/escapes1.ics'));
        $this->assertEquals(
            ['There is a comma (escaped with a baskslash) in this sentence and some important words after it, see anything here?'],
            $ical->getComponent(0)->getAttributeValues('DESCRIPTION')
        );
        $this->assertEquals(
            ['There are important words after this dash - see anything here or have the words gone?'],
            $ical->getComponent(1)->getAttributeValues('DESCRIPTION')
        );
        $this->assertEquals(
            ['mailto:a@b.c'],
            $ical->getComponent(1)->getAttributeValues('ORGANIZER')
        );
        $this->assertEquals(
            ['Foo'],
            $ical->getComponent(0)->getAttributeValues('CATEGORIES')
        );
        $this->assertEquals(
            ['Foo', 'Foo,Bar', 'Bar'],
            $ical->getComponent(1)->getAttributeValues('CATEGORIES')
        );
    }

    public function testQuotedParameters()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/quoted-params.ics'));
        $attr = $ical->getComponent(0)->getAttribute('ORGANIZER', true);
        $this->assertEquals(
            'Klä,rchen; Mül:ler',
            $attr[0]['CN']
        );
    }

    public function testVcalendar20()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/vcal20.ics'));

        $this->assertEquals(
            [
                0
                => [
                    'name' => 'PRODID',
                    'params'
                    => [
                    ],
                    'value' => '-//Google Inc//Google Calendar 70.9054//EN',
                    'values'
                    => [
                        0 => '-//Google Inc//Google Calendar 70.9054//EN',
                    ],
                ],
                1
                => [
                    'name' => 'VERSION',
                    'params'
                    => [
                    ],
                    'value' => '2.0',
                    'values'
                    => [
                        0 => '2.0',
                    ],
                ],
                2
                => [
                    'name' => 'CALSCALE',
                    'params'
                    => [
                    ],
                    'value' => 'GREGORIAN',
                    'values'
                    => [
                        0 => 'GREGORIAN',
                    ],
                ],
                3
                => [
                    'name' => 'METHOD',
                    'params'
                    => [
                    ],
                    'value' => 'PUBLISH',
                    'values'
                    => [
                        0 => 'PUBLISH',
                    ],
                ],
                4
                => [
                    'name' => 'X-WR-CALNAME',
                    'params'
                    => [
                    ],
                    'value' => 'PEAR - PHP Extension and Application Repository',
                    'values'
                    => [
                        0 => 'PEAR - PHP Extension and Application Repository',
                    ],
                ],
                5
                => [
                    'name' => 'X-WR-TIMEZONE',
                    'params'
                    => [
                    ],
                    'value' => 'Atlantic/Reykjavik',
                    'values'
                    => [
                        0 => 'Atlantic/Reykjavik',
                    ],
                ],
                6
                => [
                    'name' => 'X-WR-CALDESC',
                    'params'
                    => [
                    ],
                    'value' => 'pear.php.net activity calendar, bug triage, group meetings, qa, conferences or similar',
                    'values'
                    => [
                        0 => 'pear.php.net activity calendar, bug triage, group meetings, qa, conferences or similar',
                    ],
                ],
            ],
            $ical->getAllAttributes()
        );

        $this->assertEquals(
            [
                0
                => [
                    'name' => 'DTSTART',
                    'params'
                    => [
                    ],
                    'value' => 1224950400,
                    'values'
                    => [
                        0 => 1224950400,
                    ],
                ],
                1
                => [
                    'name' => 'DTEND',
                    'params'
                    => [
                    ],
                    'value' => 1224968400,
                    'values'
                    => [
                        0 => 1224968400,
                    ],
                ],
                2
                => [
                    'name' => 'DTSTAMP',
                    'params'
                    => [
                    ],
                    'value' => 1219138073,
                    'values'
                    => [
                        0 => 1219138073,
                    ],
                ],
                3
                => [
                    'name' => 'UID',
                    'params'
                    => [
                    ],
                    'value' => 'ntnrt4go4482q2trk18bt62c0o@google.com',
                    'values'
                    => [
                        0 => 'ntnrt4go4482q2trk18bt62c0o@google.com',
                    ],
                ],
                4
                => [
                    'name' => 'RECURRENCE-ID',
                    'params'
                    => [
                    ],
                    'value' => 1224950400,
                    'values'
                    => [
                        0 => 1224950400,
                    ],
                ],
                5
                => [
                    'name' => 'CLASS',
                    'params'
                    => [
                    ],
                    'value' => 'PUBLIC',
                    'values'
                    => [
                        0 => 'PUBLIC',
                    ],
                ],
                6
                => [
                    'name' => 'CREATED',
                    'params'
                    => [
                    ],
                    'value' => 1204763165,
                    'values'
                    => [
                        0 => 1204763165,
                    ],
                ],
                7
                => [
                    'name' => 'DESCRIPTION',
                    'params'
                    => [
                    ],
                    'value' => "Bug Triage session\r
\r
Not been invited ? Want to attend ? Let us know and we'll add you!",
                    'values'
                    => [
                        0 => "Bug Triage session\r
\r
Not been invited ? Want to attend ? Let us know and we'll add you!",
                    ],
                ],
                8
                => [
                    'name' => 'LAST-MODIFIED',
                    'params'
                    => [
                    ],
                    'value' => 1216413606,
                    'values'
                    => [
                        0 => 1216413606,
                    ],
                ],
                9
                => [
                    'name' => 'LOCATION',
                    'params'
                    => [
                    ],
                    'value' => '#pear-bugs Efnet',
                    'values'
                    => [
                        0 => '#pear-bugs Efnet',
                    ],
                ],
                10
                => [
                    'name' => 'SEQUENCE',
                    'params'
                    => [
                    ],
                    'value' => 2,
                    'values'
                    => [
                        0 => 2,
                    ],
                ],
                11
                => [
                    'name' => 'STATUS',
                    'params'
                    => [
                    ],
                    'value' => 'CONFIRMED',
                    'values'
                    => [
                        0 => 'CONFIRMED',
                    ],
                ],
                12
                => [
                    'name' => 'SUMMARY',
                    'params'
                    => [
                    ],
                    'value' => 'Bug Triage',
                    'values'
                    => [
                        0 => 'Bug Triage',
                    ],
                ],
                13
                => [
                    'name' => 'TRANSP',
                    'params'
                    => [
                    ],
                    'value' => 'OPAQUE',
                    'values'
                    => [
                        0 => 'OPAQUE',
                    ],
                ],
                14
                => [
                    'name' => 'CATEGORIES',
                    'params'
                    => [
                    ],
                    'value' => 'foo,bar,fuz buz,blah, blah',
                    'values'
                    => [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'fuz buz',
                        3 => 'blah, blah',
                    ],
                ],
            ],
            $ical->getComponent(0)->getAllAttributes()
        );
    }

    public function testBug7423()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/bug7423.ics'));
        $this->assertEquals(
            ['SUMMARY' => 'birthday'],
            $ical->getComponent(0)->toHash(true)
        );
    }

    public function testBug14153()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/bug14153.ics'));
        $params = $ical->getComponent(1)->getAttribute('DTSTART', true);
        $tz = $params[0]['TZID'];
        $start = $ical->getComponent(1)->getAttribute('DTSTART');
        $dtstart = new Horde_Date($start, $tz);
        $this->assertEquals((string) $dtstart, '2015-10-03 15:00:00');
    }

    public function testBug14132()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/bug14132.ics'));
        $params = $ical->getComponent(1)->getAttribute('DTSTART', true);
        $tz = $params[0]['TZID'];
        $start = $ical->getComponent(1)->getAttribute('DTSTART');
        $dtstart = new Horde_Date($start, $tz);
        $this->assertEquals((string) $dtstart, '2015-10-09 03:00:00');
    }

    public function testBug14132_2()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/bug14132_2.ics'));
        $params = $ical->getComponent(1)->getAttribute('DTSTART', true);
        $tz = $params[0]['TZID'];
        $start = $ical->getComponent(1)->getAttribute('DTSTART');
        $dtstart = new Horde_Date($start, $tz);
        $this->assertEquals((string) $dtstart, '2015-10-09 03:00:00');
    }

}
