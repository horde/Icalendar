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

        // SHOW: LOADING a new-form geo tag
        $this->assertStringContainsString('GEO;TYPE=work:geo:', $this->loaded->exportVcalendar());
    }

    public function testSupportImpp()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        // SHOW: Can correctly read and write property
        $this->assertStringNotContainsString('IMPP:', $blank->exportVcalendar());
        $blank->setAttribute('IMPP', 'xmpp:alice@example.com');
        $this->assertStringContainsString('IMPP:xmpp:alice@example.com', $blank->exportVcalendar());
        // With PREF
        $blank->setAttribute('IMPP', 'xmpp:alice@example.com', ['PREF' => "1"], false);
        $this->assertStringContainsString('IMPP;PREF=1:xmpp:alice@example.com', $blank->exportVcalendar());
    }

    public function testSupportKey()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('KEY:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('KEY', 'http://www.example.com/keys/jdoe.cer');
        $this->assertStringContainsString('KEY:http://www.example.com/keys/jdoe.cer', $blank->exportVcalendar());
    }

    public function testSupportKind()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('KIND:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('KIND', 'individual', [], false);
        $this->assertStringContainsString('KIND:individual', $blank->exportVcalendar());
        $blank->setAttribute('KIND', 'group', [], false);
        $this->assertStringContainsString('KIND:group', $blank->exportVcalendar());
        $blank->setAttribute('KIND', 'org', [], false);
        $this->assertStringContainsString('KIND:org', $blank->exportVcalendar());
        $blank->setAttribute('KIND', 'location', [], false);
        $this->assertStringContainsString('KIND:location', $blank->exportVcalendar());
        
    }

    public function testLabelToAdrProperty()
    {
        // SHOW: Is not present in blank object
        $blank = $this->blank;
        $this->assertStringNotContainsString('LABEL:', $blank->exportVcalendar());
        // SHOW: We do not write the LABEL key anymore
        // On Purpose: Literal newline escapes NOT expanded via ""
        $blank->setAttribute('LABEL', 'Sonnenstr. 22\n80331 München\nDeutschland');
        $this->assertStringNotContainsString('LABEL:', $blank->exportVcalendar());
        // SHOW: If a LABEL is provided, we will write out ADR;LABEL
        $this->assertStringContainsString('ADR;LABEL="Sonnenstr. 22\n80331 München\nDeutschland"', $blank->exportVcalendar());
    }

    public function testSupportLang()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('LANG:', $blank->exportVcalendar());

        // SHOW: Multiple languages possible
        $blank->setAttribute('LANG', 'de', ['TYPE' => 'home']);
        $blank->setAttribute('LANG', 'de', ['TYPE' => 'work', 'PREF' => '1' ]);
        $blank->setAttribute('LANG', 'en', ['TYPE' => 'work']);
        $blank->setAttribute('LANG', 'fr');
        $this->assertStringContainsString('LANG;TYPE=home:de', $blank->exportVcalendar());
        $this->assertStringContainsString('LANG;TYPE=work;PREF=1:de', $blank->exportVcalendar());
        $this->assertStringContainsString('LANG;TYPE=work:en', $blank->exportVcalendar());
        $this->assertStringContainsString('LANG:fr', $blank->exportVcalendar());
    }

    public function testSupportLogo()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('LOGO:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('LOGO', 'http://www.example.com/pub/logos/abccorp.jpg');
        $this->assertStringContainsString('LOGO:http://www.example.com/pub/logos/abccorp.jpg', $blank->exportVcalendar());
    }

    public function testNoMoreMailerProperty()
    {
        // We do not write this property
        // No designated replacement
        $blank = $this->blank;
        $this->assertStringNotContainsString('MAILER:', $blank->exportVcalendar());
        $blank->setAttribute('MAILER', 'Norman Mailer');
        $this->assertStringNotContainsString('MAILER:', $blank->exportVcalendar());
    }

    public function testSupportMemberIfGroup()
    {
        $blank = $this->blank;
        // SHOW: Is optional
        $this->assertStringNotContainsString('KIND:', $blank->exportVcalendar());
        $this->assertStringNotContainsString('MEMBER:', $blank->exportVcalendar());
        $blank->setAttribute('MEMBER', 'mailto:subscriber1@example.com');
        $this->assertStringContainsString('KIND:group', $blank->exportVcalendar());
        $this->assertStringContainsString('MEMBER:mailto:subscriber1@example.com', $blank->exportVcalendar());
    }

    public function testSupportN()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString("\nN:", $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('N', '', [], true, ['LAST', 'JAMES', null, null, 'sr.']);
        $this->assertStringContainsString('N:LAST;JAMES;;;sr.', $blank->exportVcalendar());
        // SHOW: Can only exist once
        $blank->setAttribute('N', '', [], true, ['LAST', 'JAMES']);
        $this->assertStringContainsString('N:LAST;JAMES;;;', $blank->exportVcalendar());
        $blank->setAttribute('N', '', [], true, ['Coverdale']);
        $this->assertStringContainsString('N:Coverdale;;;;', $blank->exportVcalendar());
        // Show setting only a last name as a single property.
        $blank->setAttribute('N', 'Washington');
        $this->assertStringContainsString('N:Washington;;;;', $blank->exportVcalendar());
    }

    public function testNoMoreNameProperty()
    {
        $blank = $this->blank;
        // We do not write this property
        // No designated replacement
        $blank->setAttribute('NAME', 'De la Vega');
        $this->assertStringNotContainsString('NAME:', $blank->exportVcalendar());
    }

    public function testSupportNote()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('NOTE:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('NOTE', 'Framework 6.0 is coming');
        $this->assertStringContainsString('NOTE:Framework 6.0 is coming', $blank->exportVcalendar());
    }

    public function testSupportOrg()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('ORG:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('ORG', 'Horde LLC');
        $this->assertStringContainsString('ORG:Horde LLC', $blank->exportVcalendar());
        // SHOW: Correctness of multipart ; 
        $blank->setAttribute('ORG', '', [], false, ['ACME corp', 'Coyote Wear', 'Financial']);
        $this->assertStringContainsString('ORG:ACME corp;Coyote Wear;Financial', $blank->exportVcalendar());
    }

    public function testSupportPhoto()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('PHOTO:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('PHOTO', 'http://www.example.com/pub/photos/jqpublic.gif');
        $this->assertStringContainsString('PHOTO:http://www.example.com/pub/photos/jqpublic.gif', $blank->exportVcalendar());
    }

    public function testSupportProdid()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('PRODID:', $blank->exportVcalendar());
        // SHOW: Can only exist once
        $blank->setAttribute('PRODID', 'SOME PRODUCT');
        $blank->setAttribute('PRODID', 'OR OTHER');
        $this->assertStringNotContainsString('PRODID:SOME PRODUCT', $blank->exportVcalendar());
        $this->assertStringContainsString('PRODID:OR OTHER', $blank->exportVcalendar());
    }

    public function testNoMoreProfile()
    {
        // SHOW: We will not set this option for VCard 4.0
        $blank = $this->blank;
        $blank->setAttribute('PROFILE', 'VCARD');
        $this->assertStringNotContainsString('PROFILE:', $blank->exportVcalendar());
    }

    public function testSupportRelated()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('RELATED:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('RELATED', 'http://www.example.com/keys/jdoe.cer');
        $this->assertStringContainsString('RELATED:http://www.example.com/keys/jdoe.cer', $blank->exportVcalendar());
    }

    public function testSupportRev()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('REV:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('REV', '19951031T222710Z');
        $this->assertStringContainsString('REV:19951031T222710Z', $blank->exportVcalendar());
    }

    public function testSupportRole()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('ROLE:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('ROLE', 'Team Lead');
        $this->assertStringContainsString('ROLE:Team Lead', $blank->exportVcalendar());
    }

    public function testNoMoreSortstring()
    {
        // If version is vcard 4.0 and sort-string is provided
        $blank = $this->blank;
        // No SORT-STRING is written
        $blank->setAttribute('SORT-STRING', 'MIR');
        $this->assertStringNotContainsString('SORT-STRING:', $blank->exportVcalendar());

        // TODO: Would be nice to autoconvert, look for existing N and ORG properties
        // and amend them with SORT-AS parameter
    }

    public function testSupportSound()
    {
        $soundUri = 'CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@example.com';
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('SOUND:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('SOUND', $soundUri);
        $this->assertStringContainsString('SOUND:' . $soundUri, $blank->exportVcalendar());
    }

    public function testSupportSource()
    {
        $sourceUri = 'http://directory.example.com/addressbooks/jdoe/Jean%20Dupont.vcf';
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('SOURCE:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('SOURCE', $sourceUri);
        $this->assertStringContainsString('SOURCE:' . $sourceUri, $blank->exportVcalendar());
    }

    public function testSupportTel()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('TEL:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('TEL', 'tel:+33-01-23-45-67', ['VALUE' => 'uri', 'PREF' => '1', 'TYPE' => 'home']);
        $this->assertStringContainsString('TEL;VALUE=uri;PREF=1;TYPE=home:tel:+33-01-23-45-67', $blank->exportVcalendar());
    }

    public function testSupportTitle()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('TITLE:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('TITLE', 'Rampage Master');
        $this->assertStringContainsString('TITLE:Rampage Master', $blank->exportVcalendar());
    }

    public function testSupportTz()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('TZ:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('TZ', 'Europe/Berlin');
        $this->assertStringContainsString('TZ:Europe/Berlin', $blank->exportVcalendar());
        // TODO: Show handling if we input legacy format
    }

    public function testSupportUid()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('UID:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('UID', 'urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6');
        $blank->setAttribute('UID', 'urn:uuid:f81d4fae-7dec-11d0-a755-00a0c91e6bf6');
        $this->assertStringNotContainsString('UID:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6', $blank->exportVcalendar());
        $this->assertStringContainsString('UID:urn:uuid:f81d4fae-7dec-11d0-a755-00a0c91e6bf6', $blank->exportVcalendar());
    }

    public function testSupportUrl()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('URL:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('URL', 'https://www.horde.org');
        $this->assertStringContainsString('URL:https://www.horde.org', $blank->exportVcalendar());
    }

    public function testVersionMandatory()
    {
        $blank = $this->blank;
        // SHOW: Version is always present
        $this->assertStringContainsString('VERSION:', $blank->exportVcalendar());
        // SHOW: Regardless of order, version is always right after BEGIN
        $blank->setAttribute('URL', 'https://www.horde.org');
        $blank->setAttribute('VERSION:', '4.0');
        $expect = "BEGIN:VCARD\r\nVERSION:4.0\r\n";
        $this->assertStringContainsString($expect, $blank->exportVcalendar());
    }

    public function testSupportXml()
    {
        // SHOW: Is optional
        $blank = $this->blank;
        $this->assertStringNotContainsString('XML:', $blank->exportVcalendar());
        // SHOW: Can correctly read and write property
        $blank->setAttribute('XML', '<xml></xml>');
        $this->assertStringContainsString('XML:<xml></xml>', $blank->exportVcalendar());
    }

    // Tests for RFC 6474 vCard Format Extensions: Place of Birth, Place and Date of Death
    // TODO
    // TEST for RFC 6715 https://tools.ietf.org/html/rfc6715 OMA CAB extensions
    // TEST for RFC 4770 https://tools.ietf.org/html/rfc4770 Instant Messengers
    // TEST for select X-formats for well-documented other clients

}
