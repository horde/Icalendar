<?php
/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 */
namespace Horde\Icalendar;
use \Horde_Test_Case;
use \Horde_Icalendar_Vcard;
use \Horde_Icalendar;
use \Horde_Icalendar_Exception;

/**
 * @category   Horde
 * @package    Icalendar
 * @subpackage UnitTests
 */
class VcardV4Test extends Horde_Test_Case
{
    public function setUp(): void
    {
        date_default_timezone_set('UTC');
        $this->blank = new Horde_Icalendar_Vcard('4.0');
        $ical = new Horde_Icalendar_Vcard('4.0');
        $ical->parsevCalendar(file_get_contents(__DIR__ . '/fixtures/vcard4.0.vcs'));
        $this->loaded = $ical;
    }

    // Tests for V4.0 properties
    public function testNoMoreAgentProperty()
    {
        // Use RELATED:agent rather than AGENT
        $blank = $this->blank;
        $blank->setAttribute('AGENT', 'Mr. Assistant');
        $this->assertStringNotContainsString('AGENT:', $blank->exportVcalendar());
        $this->assertStringContainsString('RELATED;type=agent:', $blank->exportVcalendar());
    }

    public function testSupportAnniversary()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write anniversaries
        $this->assertStringNotContainsString('ANNIVERSARY:', $blank->exportVcalendar());
        $blank->setAttribute('ANNIVERSARY', '2020-04-11');
        $this->assertStringContainsString('ANNIVERSARY:2020-04-11', $blank->exportVcalendar());
    }

    public function testSupportBday()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('BDAY:', $blank->exportVcalendar());
        $blank->setAttribute('BDAY', '1983-04-11');
        $this->assertStringContainsString('BDAY:1983-04-11', $blank->exportVcalendar());
    }

    public function testWriteBeginEnd()
    {
        $str = $this->blank->exportVcalendar();
        $this->assertStringStartsWith('BEGIN:VCARD', $str);
        $this->assertStringEndsWith("END:VCARD\r\n", $str);
        // SHOW: produced vcards have BEGIN first and END last
    }

    public function testSupportCaladruri()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('CALADRURI:', $blank->exportVcalendar());
        $blank->setAttribute('CALADRURI', 'https://turba.horde.org/carddav');
        $this->assertStringContainsString('CALADRURI:https://turba.horde.org/carddav', $blank->exportVcalendar());
    }

    public function testSupportCaluri()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('CALURI:', $blank->exportVcalendar());
        $blank->setAttribute('CALURI', 'https://turba.horde.org/carddav');
        $this->assertStringContainsString('CALURI:https://turba.horde.org/carddav', $blank->exportVcalendar());
    }

    public function testSupportCategories()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('CATEGORIES', $blank->exportVcalendar());
        $blank->setAttribute('CATEGORIES', '', [], true, ['developer', 'maintainer']);
        $this->assertStringContainsString('CATEGORIES:developer,maintainer', $blank->exportVcalendar());
    }

    public function testNoMoreClassProperty()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('CLASS', $blank->exportVcalendar());
        $blank->setAttribute('CLASS', 'public');
        $this->assertStringNotContainsString('CLASS:', $blank->exportVcalendar());
    }

    public function testSupportClientpidmap()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('CLIENTPIDMAP', $blank->exportVcalendar());
        $blank->setAttribute('CLIENTPIDMAP', null, [], true, [1, 'urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b']);
        $this->assertStringContainsString('CLIENTPIDMAP:1;urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b', $blank->exportVcalendar());
    }

    public function testSupportEmail()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('EMAIL:', $blank->exportVcalendar());
        $blank->setAttribute('EMAIL', 'lang@b1-systems.de');
        $this->assertStringContainsString('EMAIL:lang@b1-systems.de', $blank->exportVcalendar());
    }

    public function testSupportFburl()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('FBURL:', $blank->exportVcalendar());
        $blank->setAttribute('FBURL', 'http://example.org/fb/janedone');
        $this->assertStringContainsString('FBURL:http://example.org/fb/janedone', $blank->exportVcalendar());
    }
    public function testMandatoryFnProperty()
    {
        $blank = $this->blank;
        // SHOW: This will be present even if not explicitly set
        $this->assertStringContainsString('FN:', $blank->exportvCalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('FN', 'Dr. Nomen Nemo');
        $this->assertStringContainsString('FN:Dr. Nomen Nemo', $blank->exportvCalendar());
        $this->assertStringContainsString("FN:Dr. Firstname Middle Names Lastname, sr.\r\n", $this->loaded->exportvCalendar());
    }

    public function testSupportGender()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('GENDER:', $blank->exportVcalendar());
        $blank->setAttribute('GENDER', 'F');
        $this->assertStringContainsString('GENDER:F', $blank->exportVcalendar());
    }

    public function testSupportGeo()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('GEO:', $blank->exportVcalendar());
        // Old way:
        $blank->setAttribute('GEO', '', ['TYPE' => 'home'], true, ["30.0", "60.1"]);
        $this->assertStringContainsString('GEO;TYPE=home:geo:30.0,60.1', $blank->exportVcalendar());

        // Modern way: Provide URI
        // WITHOUT TYPE
        $blank->setAttribute('GEO', 'geo:20.0,40.0');
        $this->assertStringContainsString('GEO:geo:', $blank->exportVcalendar());

        // WITH TYPE
        $blank->setAttribute('GEO', 'geo:0.0,0.0', ['TYPE' => 'work'], true, []);
        $this->assertStringContainsString('GEO;TYPE=work:geo:', $blank->exportVcalendar());

        // SHOW: V4 vcards use \, and V3 uses ,

        // SHOW: LOADING a new-form geo tag
        $this->assertStringContainsString('GEO;TYPE=work:geo:', $this->loaded->exportVcalendar());

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportImpp()
    {
        // SHOW: Is optional
        // SHOW: Can correctly read and write property

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportKey()
    {
        // SHOW: Is optional
        // SHOW: Can correctly read and write property

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportKind()
    {
        // SHOW: Is optional
        // SHOW: Can correctly read and write property

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testLabelToAdrProperty()
    {
        // SHOW: We do not write the LABEL key anymore
        // SHOW: If a LABEL is provided, we will write out ADR;LABEL
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

    }
    public function testSupportLang()
    {
        // SHOW: Is optional
        // SHOW: Can correctly read and write property

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportLogo()
    {
        // SHOW: Is optional
        // SHOW: Can correctly read and write property

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testNoMoreMailerProperty()
    {
        // We do not write this property
        // No designated replacement
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportMemberIfGroup()
    {
        // SHOW: Is optional
        // SHOW: Only if KIND is present and GROUP
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportN()
    {
        // SHOW: Is optional
        // SHOW: Only if KIND is present and GROUP
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testNoMoreNameProperty()
    {
        // We do not write this property
        // No designated replacement
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportNote()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportOrg()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportPhoto()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportProdid()
    {
        // SHOW: Is optional
        // SHOW: We set it on export
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testNoMoreProfile()
    {
        // SHOW: Only 
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportRelated()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportRev()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportRole()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testNoMoreSortstring()
    {
        // If version is vcard 4.0 and sort-string is provided
        // No SORT-STRING is written
        // instead, look for existing N and ORG properties
        // and amend them with SORT-AS parameter
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportSound()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportSource()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportTel()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportTitle()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportTz()
    {
        // SHOW: Is optional
        // SHOW: We write the 4.0 representation
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportUid()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testSupportUrl()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    public function testVersionMandatory()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        // SHOW: Regardless of order, version is always right after BEGIN
        // SHOW: Version is always present
    }
    public function testSupportXml()
    {
        // SHOW: Is optional
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    // Tests for RFC 6474 vCard Format Extensions: Place of Birth, Place and Date of Death
    // TODO
    // TEST for RFC 6715 https://tools.ietf.org/html/rfc6715 OMA CAB extensions
    // TEST for RFC 4770 https://tools.ietf.org/html/rfc4770 Instant Messengers
    // TEST for select X-formats for well-documented other clients

}
