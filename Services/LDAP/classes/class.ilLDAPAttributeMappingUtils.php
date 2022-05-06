<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * A collection of static utility functions for LDAP attribute mapping
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPAttributeMappingUtils
{
    /**
     * Get mapping rule by objectClass
     * @param string $a_class
     * @return array<string, string>
     */
    public static function _getMappingRulesByClass(string $a_class) : array
    {
        $mapping_rule = [];

        switch ($a_class) {
            case 'inetOrgPerson':
                $mapping_rule['firstname'] = 'givenName';
                $mapping_rule['institution'] = 'o';
                $mapping_rule['department'] = 'departmentNumber';
                $mapping_rule['phone_home'] = 'homePhone';
                $mapping_rule['phone_mobile'] = 'mobile';
                $mapping_rule['email'] = 'mail';
                $mapping_rule['photo'] = 'jpegPhoto';
            // no break since it inherits from organizationalPerson and person

            case 'organizationalPerson':
                $mapping_rule['fax'] = 'facsimileTelephoneNumber';
                $mapping_rule['title'] = 'title';
                $mapping_rule['street'] = 'street';
                $mapping_rule['zipcode'] = 'postalCode';
                $mapping_rule['city'] = 'l';
                $mapping_rule['country'] = 'st';
            // no break since it inherits from person

            case 'person':
                $mapping_rule['lastname'] = 'sn';
                $mapping_rule['phone_office'] = 'telephoneNumber';
                break;

            case 'ad_2003':
                $mapping_rule['firstname'] = 'givenName';
                $mapping_rule['lastname'] = 'sn';
                $mapping_rule['title'] = 'title';
                $mapping_rule['institution'] = 'company';
                $mapping_rule['department'] = 'department';
                $mapping_rule['phone_home'] = 'telephoneNumber';
                $mapping_rule['phone_mobile'] = 'mobile';
                $mapping_rule['email'] = 'mail';
                $mapping_rule['street'] = 'streetAddress';
                $mapping_rule['city'] = 'l,st';
                $mapping_rule['country'] = 'co';
                $mapping_rule['zipcode'] = 'postalCode';
                $mapping_rule['fax'] = 'facsimileTelephoneNumber';
                break;
        }

        return $mapping_rule;
    }
}
