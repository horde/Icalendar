<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Contact;

use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\Contact\VCard;
use Horde\Icalendar\Value\Organization;
use Horde\Icalendar\Value\StructuredAddress;
use Horde\Icalendar\Value\StructuredName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VCard::class)]
#[CoversClass(StructuredName::class)]
#[CoversClass(StructuredAddress::class)]
#[CoversClass(Organization::class)]
final class VCardTest extends TestCase
{
    // Scalar properties

    #[Test]
    public function scalarGettersAndSetters(): void
    {
        $card = new VCard();
        $card->setFn('John Doe');
        $card->setUid('urn:uuid:12345');
        $card->setTitle('Engineer');
        $card->setRole('Developer');
        $card->setNote('A note');
        $card->setProdid('-//Test//EN');
        $card->setRev('20260521T120000Z');

        $this->assertSame('John Doe', $card->getFn());
        $this->assertSame('urn:uuid:12345', $card->getUid());
        $this->assertSame('Engineer', $card->getTitle());
        $this->assertSame('Developer', $card->getRole());
        $this->assertSame('A note', $card->getNote());
        $this->assertSame('-//Test//EN', $card->getProdid());
        $this->assertSame('20260521T120000Z', $card->getRev());
    }

    #[Test]
    public function kindProperty(): void
    {
        $card = new VCard();
        $this->assertNull($card->getKind());

        $card->setKind('individual');
        $this->assertSame('individual', $card->getKind());
    }

    #[Test]
    public function dateProperties(): void
    {
        $card = new VCard();
        $this->assertNull($card->getBirthday());
        $this->assertNull($card->getAnniversary());

        $card->setBirthday('19900115');
        $card->setAnniversary('20150620');

        $this->assertSame('19900115', $card->getBirthday());
        $this->assertSame('20150620', $card->getAnniversary());
    }

    // Structured name (N)

    #[Test]
    public function structuredNameAccessors(): void
    {
        $card = new VCard();
        $this->assertNull($card->getName());

        $name = StructuredName::create('Doe', 'John', 'M.', 'Mr.', 'Jr.');
        $card->setName($name);

        $retrieved = $card->getName();
        $this->assertNotNull($retrieved);
        $this->assertSame('Doe', $retrieved->getFamily());
        $this->assertSame('John', $retrieved->getGiven());
        $this->assertSame('M.', $retrieved->getAdditional());
        $this->assertSame('Mr.', $retrieved->getPrefix());
        $this->assertSame('Jr.', $retrieved->getSuffix());
    }

    #[Test]
    public function structuredNameMinimalParts(): void
    {
        $name = StructuredName::create('Smith', 'Jane');
        $this->assertSame('Smith', $name->getFamily());
        $this->assertSame('Jane', $name->getGiven());
        $this->assertSame('', $name->getAdditional());
        $this->assertSame('', $name->getPrefix());
        $this->assertSame('', $name->getSuffix());
    }

    // Structured address (ADR)

    #[Test]
    public function structuredAddressAccessors(): void
    {
        $card = new VCard();
        $this->assertSame([], $card->getAddresses());

        $addr = StructuredAddress::create(
            '',
            '',
            '123 Main St',
            'Anytown',
            'CA',
            '90210',
            'USA',
        );
        $card->addAddress($addr);

        $addresses = $card->getAddresses();
        $this->assertCount(1, $addresses);
        $this->assertSame('', $addresses[0]->getPobox());
        $this->assertSame('', $addresses[0]->getExtended());
        $this->assertSame('123 Main St', $addresses[0]->getStreet());
        $this->assertSame('Anytown', $addresses[0]->getLocality());
        $this->assertSame('CA', $addresses[0]->getRegion());
        $this->assertSame('90210', $addresses[0]->getPostalcode());
        $this->assertSame('USA', $addresses[0]->getCountry());
    }

    #[Test]
    public function multipleAddresses(): void
    {
        $card = new VCard();
        $card->addAddress(StructuredAddress::create('', '', '123 Main St', 'Town1'));
        $card->addAddress(StructuredAddress::create('', '', '456 Oak Ave', 'Town2'));

        $this->assertCount(2, $card->getAddresses());
        $this->assertSame('123 Main St', $card->getAddresses()[0]->getStreet());
        $this->assertSame('456 Oak Ave', $card->getAddresses()[1]->getStreet());
    }

    // Organization (ORG)

    #[Test]
    public function organizationAccessors(): void
    {
        $card = new VCard();
        $this->assertSame([], $card->getOrganizations());

        $org = Organization::create('Horde LLC', 'Engineering', 'Backend');
        $card->addOrganization($org);

        $orgs = $card->getOrganizations();
        $this->assertCount(1, $orgs);
        $this->assertSame('Horde LLC', $orgs[0]->getOrganization());
        $this->assertSame('Engineering', $orgs[0]->getDepartment());
        $this->assertSame(['Horde LLC', 'Engineering', 'Backend'], $orgs[0]->getParts());
    }

    // Multi-string properties

    #[Test]
    public function emailMultiOccurrence(): void
    {
        $card = new VCard();
        $this->assertSame([], $card->getEmails());

        $card->addEmail('john@example.com');
        $card->addEmail('j.doe@work.com');

        $emails = $card->getEmails();
        $this->assertCount(2, $emails);
        $this->assertSame('john@example.com', $emails[0]);
        $this->assertSame('j.doe@work.com', $emails[1]);
    }

    #[Test]
    public function telephoneMultiOccurrence(): void
    {
        $card = new VCard();
        $card->addTelephone('+1-555-1234');
        $card->addTelephone('+1-555-5678');

        $this->assertCount(2, $card->getTelephones());
    }

    #[Test]
    public function urlMultiOccurrence(): void
    {
        $card = new VCard();
        $card->addUrl('https://example.com');
        $card->addUrl('https://blog.example.com');

        $urls = $card->getUrls();
        $this->assertCount(2, $urls);
        $this->assertSame('https://example.com', $urls[0]);
    }

    #[Test]
    public function categoriesProperty(): void
    {
        $card = new VCard();
        $this->assertSame([], $card->getCategories());

        $card->addCategory('Friends');
        $card->addCategory('Colleagues');

        $this->assertSame(['Friends', 'Colleagues'], $card->getCategories());
    }

    // Build and serialize

    #[Test]
    public function buildAndSerialize(): void
    {
        $card = new VCard();
        $card->setProperty('VERSION', '4.0');
        $card->setFn('Jane Smith');
        $card->setName(StructuredName::create('Smith', 'Jane'));
        $card->addEmail('jane@example.com');
        $card->addAddress(StructuredAddress::create('', '', '42 Elm St', 'Springfield', 'IL', '62704', 'US'));
        $card->addOrganization(Organization::create('Acme Corp'));

        $output = $card->toString();

        $this->assertStringContainsString('BEGIN:VCARD', $output);
        $this->assertStringContainsString('END:VCARD', $output);
        $this->assertStringContainsString('FN:Jane Smith', $output);
        $this->assertStringContainsString('N:Smith;Jane;;;', $output);
        $this->assertStringContainsString('EMAIL:jane@example.com', $output);
        $this->assertStringContainsString('ADR:;;42 Elm St;Springfield;IL;62704;US', $output);
        $this->assertStringContainsString('ORG:Acme Corp', $output);
    }

    // Parse and read

    #[Test]
    public function parseVcardWithStructuredProperties(): void
    {
        $raw = "BEGIN:VCARD\r\n"
            . "VERSION:4.0\r\n"
            . "FN:John Doe\r\n"
            . "N:Doe;John;M.;Dr.;III\r\n"
            . "ADR:;;100 Broadway;New York;NY;10001;USA\r\n"
            . "ORG:MegaCorp;RnD\r\n"
            . "EMAIL:john@example.com\r\n"
            . "TEL:+1-212-555-1234\r\n"
            . "URL:https://johndoe.com\r\n"
            . "BDAY:19851225\r\n"
            . "END:VCARD";

        $card = AbstractComponent::fromString($raw);
        $this->assertInstanceOf(VCard::class, $card);

        $this->assertSame('John Doe', $card->getFn());
        $this->assertSame('19851225', $card->getBirthday());

        $name = $card->getName();
        $this->assertNotNull($name);
        $this->assertSame('Doe', $name->getFamily());
        $this->assertSame('John', $name->getGiven());
        $this->assertSame('M.', $name->getAdditional());
        $this->assertSame('Dr.', $name->getPrefix());
        $this->assertSame('III', $name->getSuffix());

        $addresses = $card->getAddresses();
        $this->assertCount(1, $addresses);
        $this->assertSame('100 Broadway', $addresses[0]->getStreet());
        $this->assertSame('New York', $addresses[0]->getLocality());
        $this->assertSame('NY', $addresses[0]->getRegion());
        $this->assertSame('10001', $addresses[0]->getPostalcode());
        $this->assertSame('USA', $addresses[0]->getCountry());

        $orgs = $card->getOrganizations();
        $this->assertCount(1, $orgs);
        $this->assertSame('MegaCorp', $orgs[0]->getOrganization());
        $this->assertSame('RnD', $orgs[0]->getDepartment());

        $this->assertSame(['john@example.com'], $card->getEmails());
        $this->assertSame(['+1-212-555-1234'], $card->getTelephones());
        $this->assertSame(['https://johndoe.com'], $card->getUrls());
    }

    // Round-trip

    #[Test]
    public function roundTrip(): void
    {
        $card = new VCard();
        $card->setProperty('VERSION', '4.0');
        $card->setFn('Round Trip Test');
        $card->setName(StructuredName::create('Trip', 'Round', '', '', ''));
        $card->addEmail('rt@example.com');
        $card->addAddress(StructuredAddress::create('', '', '1 Test Ln', 'Testville', 'TX', '75001', 'US'));

        $serialized = $card->toString();
        $parsed = AbstractComponent::fromString($serialized);
        $this->assertInstanceOf(VCard::class, $parsed);

        $this->assertSame('Round Trip Test', $parsed->getFn());
        $this->assertSame('Trip', $parsed->getName()->getFamily());
        $this->assertSame('Round', $parsed->getName()->getGiven());
        $this->assertSame(['rt@example.com'], $parsed->getEmails());
        $this->assertCount(1, $parsed->getAddresses());
        $this->assertSame('1 Test Ln', $parsed->getAddresses()[0]->getStreet());
    }
}
