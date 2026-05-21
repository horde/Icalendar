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

namespace Horde\Icalendar\Contact;

use Horde\Icalendar\Component\AbstractComponent;
use Horde\Icalendar\RootComponent;
use Horde\Icalendar\Value\Organization;
use Horde\Icalendar\Value\StructuredAddress;
use Horde\Icalendar\Value\StructuredName;

/**
 * vCard component with typed property accessors.
 *
 * Provides domain-typed lenses over the underlying property bag for
 * scalar, structured, multi-valued, and date vCard properties.
 */
class VCard extends AbstractComponent implements RootComponent
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'VCARD';
    }

    /**
     * Get the formatted name (FN property).
     */
    public function getFn(): ?string
    {
        return $this->getPropertyValue('FN');
    }

    /**
     * Set the formatted name (FN property).
     */
    public function setFn(string $fn): void
    {
        $this->setProperty('FN', $fn);
    }

    /**
     * Get the NOTE property value.
     */
    public function getNote(): ?string
    {
        return $this->getPropertyValue('NOTE');
    }

    /**
     * Set the NOTE property value.
     */
    public function setNote(string $note): void
    {
        $this->setProperty('NOTE', $note);
    }

    /**
     * Get the TITLE property value.
     */
    public function getTitle(): ?string
    {
        return $this->getPropertyValue('TITLE');
    }

    /**
     * Set the TITLE property value.
     */
    public function setTitle(string $title): void
    {
        $this->setProperty('TITLE', $title);
    }

    /**
     * Get the ROLE property value.
     */
    public function getRole(): ?string
    {
        return $this->getPropertyValue('ROLE');
    }

    /**
     * Set the ROLE property value.
     */
    public function setRole(string $role): void
    {
        $this->setProperty('ROLE', $role);
    }

    /**
     * Get the UID property value.
     */
    public function getUid(): ?string
    {
        return $this->getPropertyValue('UID');
    }

    /**
     * Set the UID property value.
     */
    public function setUid(string $uid): void
    {
        $this->setProperty('UID', $uid);
    }

    /**
     * Get the PRODID property value.
     */
    public function getProdid(): ?string
    {
        return $this->getPropertyValue('PRODID');
    }

    /**
     * Set the PRODID property value.
     */
    public function setProdid(string $prodid): void
    {
        $this->setProperty('PRODID', $prodid);
    }

    /**
     * Get the REV (revision timestamp) property value.
     */
    public function getRev(): ?string
    {
        return $this->getPropertyValue('REV');
    }

    /**
     * Set the REV (revision timestamp) property value.
     */
    public function setRev(string $rev): void
    {
        $this->setProperty('REV', $rev);
    }

    /**
     * Get the KIND property (individual, group, org, location).
     */
    public function getKind(): ?string
    {
        return $this->getPropertyValue('KIND');
    }

    /**
     * Set the KIND property (individual, group, org, location).
     */
    public function setKind(string $kind): void
    {
        $this->setProperty('KIND', $kind);
    }

    /**
     * Get the BDAY (birthday) property value as a date string.
     */
    public function getBirthday(): ?string
    {
        return $this->getPropertyValue('BDAY');
    }

    /**
     * Set the BDAY (birthday) property value.
     */
    public function setBirthday(string $bday): void
    {
        $this->setProperty('BDAY', $bday);
    }

    /**
     * Get the ANNIVERSARY property value as a date string.
     */
    public function getAnniversary(): ?string
    {
        return $this->getPropertyValue('ANNIVERSARY');
    }

    /**
     * Set the ANNIVERSARY property value.
     */
    public function setAnniversary(string $anniversary): void
    {
        $this->setProperty('ANNIVERSARY', $anniversary);
    }

    /**
     * Get the structured name (N property) as a typed object.
     */
    public function getName(): ?StructuredName
    {
        $prop = $this->getProperties()->get('N');
        return $prop !== null ? new StructuredName($prop) : null;
    }

    /**
     * Set the structured name (N property).
     */
    public function setName(StructuredName $name): void
    {
        $this->removeProperty('N');
        $this->getProperties()->addProperty($name->getProperty());
    }

    /**
     * Get all structured addresses (ADR properties).
     *
     * @return list<StructuredAddress>
     */
    public function getAddresses(): array
    {
        return array_map(
            fn($p) => new StructuredAddress($p),
            $this->getProperties()->getAll('ADR'),
        );
    }

    /**
     * Add a structured address (ADR property).
     */
    public function addAddress(StructuredAddress $address): void
    {
        $this->getProperties()->addProperty($address->getProperty());
    }

    /**
     * Get all organizations (ORG properties).
     *
     * @return list<Organization>
     */
    public function getOrganizations(): array
    {
        return array_map(
            fn($p) => new Organization($p),
            $this->getProperties()->getAll('ORG'),
        );
    }

    /**
     * Add an organization (ORG property).
     */
    public function addOrganization(Organization $org): void
    {
        $this->getProperties()->addProperty($org->getProperty());
    }

    /**
     * Get all email addresses (EMAIL properties).
     *
     * @return list<string>
     */
    public function getEmails(): array
    {
        return $this->getProperties()->getAllValues('EMAIL');
    }

    /**
     * Add an email address (EMAIL property).
     */
    public function addEmail(string $email): void
    {
        $this->addProperty('EMAIL', $email);
    }

    /**
     * Get all telephone numbers (TEL properties).
     *
     * @return list<string>
     */
    public function getTelephones(): array
    {
        return $this->getProperties()->getAllValues('TEL');
    }

    /**
     * Add a telephone number (TEL property).
     */
    public function addTelephone(string $tel): void
    {
        $this->addProperty('TEL', $tel);
    }

    /**
     * Get all URLs (URL properties).
     *
     * @return list<string>
     */
    public function getUrls(): array
    {
        return $this->getProperties()->getAllValues('URL');
    }

    /**
     * Add a URL (URL property).
     */
    public function addUrl(string $url): void
    {
        $this->addProperty('URL', $url);
    }

    /**
     * Get all categories as a list of strings.
     *
     * @return list<string>
     */
    public function getCategories(): array
    {
        $raw = $this->getPropertyValue('CATEGORIES');
        if ($raw === null || $raw === '') {
            return [];
        }
        return explode(',', $raw);
    }

    /**
     * Add a category to the CATEGORIES property.
     */
    public function addCategory(string $category): void
    {
        $existing = $this->getCategories();
        $existing[] = $category;
        $this->setProperty('CATEGORIES', implode(',', $existing));
    }
}
