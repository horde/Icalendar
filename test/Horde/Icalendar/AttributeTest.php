<?php
/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 */
namespace Horde\Icalendar;
use \Horde_Test_Case;
use \Horde_Icalendar;
/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 */
class AttributeTest extends Horde_Test_Case
{
    public function testDates()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/date.ics'));
        $this->assertEquals(
            0,
            $ical->getComponent(0)->getAttribute('DTSTART')
        );
        $this->assertEquals(
            'BORKED',
            $ical->getComponent(0)->getAttribute('DTEND')
        );
    }

    public function testOrg()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/org.vcf'));
        $this->assertEquals(
            array(
                'My Organization',
                'My Unit'
            ),
            $ical->getComponent(0)->getAttributeValues('ORG')
        );
    }

    public function testGeo()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/geo1.vcf'));
        $this->assertEquals(
            array(
                'latitude' => -17.87,
                'longitude' => 37.24,
            ),
            $ical->getComponent(0)->getAttribute('GEO')
        );
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/geo2.vcf'));
        $this->assertEquals(
            array(
                'latitude' => 37.386013,
                'longitude' => -122.082932,
            ),
            $ical->getComponent(0)->getAttribute('GEO')
        );
    }

    public function testAddress()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/contact.vcf'));
        $this->assertEquals(array(
                '',
                '',
                '123 Main St',
                'Smallville',
                'NJ',
                '08111',
                'United States'
            ),
            $ical->getComponent(0)->getAttributeValues('ADR')
        );
        $this->assertEquals(
            "123 Main St\nSmallville, NJ 08111",
            $ical->getComponent(0)->getAttribute('LABEL')
        );
    }

    public function testIgnoringMultipleAttributeValues()
    {
        $ical = new Horde_Icalendar();
        $ical->parsevCalendar(
            file_get_contents(__DIR__ . '/fixtures/multiple-summary.ics')
        );

        $result = $ical->getComponent(0)->getAttributeSingle('SUMMARY');

        $this->assertIsString($result);

        $this->assertEquals(
            'Summary 1',
            $result
        );
    }

}
