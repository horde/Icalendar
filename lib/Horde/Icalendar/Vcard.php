<?php
/**
 * Copyright 2003-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Karsten Fourmont <karsten@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Icalendar
 */

/**
 * Class representing vCard entries.
 *
 * @author    Karsten Fourmont <karsten@horde.org>
 * @category  Horde
 * @copyright 2003-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */
class Horde_Icalendar_Vcard extends Horde_Icalendar
{
    // The following were shamelessly yoinked from Contact_Vcard_Build
    // Part numbers for N components.
    const N_FAMILY = 0;
    const N_GIVEN = 1;
    const N_ADDL = 2;
    const N_PREFIX = 3;
    const N_SUFFIX = 4;

    // Part numbers for ADR components.
    const ADR_POB = 0;
    const ADR_EXTEND = 1;
    const ADR_STREET = 2;
    const ADR_LOCALITY = 3;
    const ADR_REGION = 4;
    const ADR_POSTCODE = 5;
    const ADR_COUNTRY = 6;

    // Part numbers for GEO components.
    const GEO_LAT = 0;
    const GEO_LON = 1;

    /**
     * The component type of this class.
     *
     * @var string
     */
    public $type = 'vcard';

    /**
     * Constructor.
     */
    public function __construct($version = '2.1')
    {
        parent::__construct($version);
    }

    /**
     * Sets the version of this component.
     *
     * @see $version
     * @see $oldFormat
     *
     * @param string  A float-like version string.
     */
    public function setVersion($version)
    {
        $this->_oldFormat = $version < 3;
        $this->_version = $version;
    }

    /**
     * Sets the value of an attribute.
     *
     * @param string $name     The name of the attribute.
     * @param string $value    The value of the attribute.
     * @param array $params    Array containing any addition parameters for
     *                         this attribute.
     * @param boolean $append  True to append the attribute, False to replace
     *                         the first matching attribute found.
     * @param array $values    Array representation of $value.  For
     *                         comma/semicolon seperated lists of values.  If
     *                         not set use $value as single array element.
     */
    public function setAttribute(
        $name, $value, $params = [],
        $append = true, $values = false
    )
    {
        if ($this->_version == '4.0') {
            // Do not accept attributes removed from the standard.
            // Rewrite to more appropriate newer representation if possible
            if ($name == 'AGENT') {
                return $this->setAttribute('RELATED', $value, ['type' => 'agent']);
            }
            if (in_array($name, ['NAME', 'MAILER', 'CLASS', 'PROFILE', 'SORT-STRING'])) {
                return;
            }
            if ($name == 'GEO') {
                // VCARD 4.0 GEO works differently
                if (!empty($values)) {
                    $value = 'geo:' . implode(',', $values);
                    $values = [];
                }
            }
            if ($name == 'KIND') {
                // There can be only one
                return parent::setAttribute($name, $value, [], false);
            }
            if ($name == 'MEMBER') {
                // MEMBER requires KIND: group
                $this->setAttribute('KIND', 'group');
            }
            if ($name == 'LABEL') {
                return $this->setAttribute('ADR', '', ['LABEL' => $value]);
            }
            if ($name == 'N') {
                if (empty($values[0])) {
                    $values[0] = $value;
                }
                // Ensure we always have 5 components
                while (count($values) < 5) {
                    $values[] = null;
                }
                return parent::setAttribute($name, $value, $params, false, $values);
            }
            if (in_array($name, ['PRODID', 'UID'])) {
                return parent::setAttribute($name, $value, $params, false, $values);
            }
        }
        return parent::setAttribute($name, $value, $params, $append, $values);
    }
    /**
     * Unlike vevent and vtodo, a vcard is normally not enclosed in an
     * iCalendar container. (BEGIN..END)
     *
     * @return string A Vcalendar representation
     */
    public function exportvCalendar()
    {
        $requiredAttributes['VERSION'] = $this->_version;
        if ($this->_version != '4.0') {
            // Not mandatory in 4.0
            $requiredAttributes['N'] = ';;;;;;';
        }
        if ($this->_version == '3.0') {
            $requiredAttributes['FN'] = '';
        }
        if ($this->_version == '4.0') {
            $requiredAttributes['FN'] = '';
        }

        foreach ($requiredAttributes as $name => $default_value) {
            try {
                $this->getAttribute($name);
            } catch (Horde_Icalendar_Exception $e) {
                $this->setAttribute($name, $default_value);
            }
        }

        return $this->_exportvData('VCARD');
    }

    /**
     * Returns the contents of the "N" tag as a printable Name:
     * i.e. converts:
     *
     *   N:Duck;Dagobert;T;Professor;Sen.
     * to
     *   "Professor Dagobert T Duck Sen"
     *
     * @return string  Full name of vcard "N" tag or null if no N tag.
     */
    public function printableName()
    {
        try {
            $name_parts = $this->getAttributeValues('N');
        } catch (Horde_Icalendar_Exception $e) {
            return null;
        }

        $name_arr = array();

        foreach (array(self::N_PREFIX, self::N_GIVEN, self::N_ADDL, self::N_FAMILY, self::N_SUFFIX) as $val) {
            if (!empty($name_parts[$val])) {
                $name_arr[] = $name_parts[$val];
            }
        }

        return implode(' ', $name_arr);
    }

    /**
     * Static function to make a given email address rfc822 compliant.
     *
     * @param string $address  An email address.
     *
     * @return string  The RFC822-formatted email address.
     */
    static function getBareEmail($address)
    {
        $ob = new Horde_Mail_Rfc822_Address($address);
        return $ob->bare_address;
    }

}
