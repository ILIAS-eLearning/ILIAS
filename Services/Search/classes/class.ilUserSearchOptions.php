<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilUserSearchOptions
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
*
*/
class ilUserSearchOptions
{
    public const FIELD_TYPE_UDF_SELECT = 1;
    public const FIELD_TYPE_UDF_TEXT = 2;
    public const FIELD_TYPE_SELECT = 3;
    public const FIELD_TYPE_TEXT = 4;
    // begin-patch lok
    public const FIELD_TYPE_MULTI = 5;
    // end-patch lok
    public const FIELD_TYPE_UDF_WYSIWYG = 6;


    /**
     * Get info of searchable fields for selectable columns in table gui
     * @param bool $a_admin
     * @return array
     */
    public static function getSelectableColumnInfo(bool $a_admin = false) : array
    {
        $col_info = array();
        foreach (self::_getSearchableFieldsInfo($a_admin) as $field) {
            if (is_numeric($field['db'])) {
                $field['db'] = 'udf_' . $field['db'];
            }

            $col_info[$field['db']] = array(
                'txt' => $field['lang']
            );

            if ($field['db'] == 'login' or $field['db'] == 'firstname' or $field['db'] == 'lastname') {
                $col_info[$field['db']]['default'] = true;
            }
        }
        return $col_info;
    }

    public static function _getSearchableFieldsInfo(bool $a_admin = false) : array
    {
        global $DIC;

        $lng = $DIC->language();

        // begin-patch lok
        $lng->loadLanguageModule('user');
        // end-patch lok

        $counter = 1;
        $fields = [];
        foreach (ilUserSearchOptions::_getPossibleFields($a_admin) as $field) {
            // TODO: check enabled
            // DONE
            if ($a_admin == false and !ilUserSearchOptions::_isEnabled($field)) {
                continue;
            }
            $fields[$counter]['values'] = array();
            $fields[$counter]['type'] = self::FIELD_TYPE_TEXT;
            $fields[$counter]['lang'] = $lng->txt($field);
            $fields[$counter]['db'] = $field;

            /**
             * @todo: implement a more general solution
             */
            $fields[$counter]['autoComplete'] = false;
            switch ($field) {
                case 'login':
                case 'firstname':
                case 'lastname':
                case 'email':
                case 'second_email':
                    $fields[$counter]['autoComplete'] = true;
                    break;
                
                case 'title':
                    $fields[$counter]['lang'] = $lng->txt('person_title');
                    break;
                
                // SELECTS
                
                case 'gender':
                    $fields[$counter]['type'] = self::FIELD_TYPE_SELECT;
                    $fields[$counter]['values'] = array(
                        0 => $lng->txt('please_choose'),
                        'n' => $lng->txt('gender_n'),
                        'f' => $lng->txt('gender_f'),
                        'm' => $lng->txt('gender_m'),
                    );
                    break;
                
                case 'sel_country':
                    $fields[$counter]['type'] = self::FIELD_TYPE_SELECT;
                    $fields[$counter]['values'] = array(0 => $lng->txt('please_choose'));
                    
                    // #7843 -- see ilCountrySelectInputGUI
                    $lng->loadLanguageModule('meta');
                    foreach (ilCountry::getCountryCodes() as $c) {
                        $fields[$counter]['values'][$c] = $lng->txt('meta_c_' . $c);
                    }
                    asort($fields[$counter]['values']);
                    break;
                    
                case 'org_units':
                    $fields[$counter]['type'] = self::FIELD_TYPE_SELECT;

                                        $paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();

                    $options[0] = $lng->txt('select_one');
                    foreach ($paths as $org_ref_id => $path) {
                        $options[$org_ref_id] = $path;
                    }
                    
                    $fields[$counter]['values'] = $options;
                    break;
                        
                    
                // begin-patch lok
                case 'interests_general':
                case 'interests_help_offered':
                case 'interests_help_looking':
                    $fields[$counter]['type'] = self::FIELD_TYPE_MULTI;
                    break;
            }
            
            ++$counter;
        }
        $fields = ilUserSearchOptions::__appendUserDefinedFields($fields, $counter);

        return $fields ?: array();
    }

    public static function _getPossibleFields(bool $a_admin = false) : array
    {
        return array('gender',
                     'lastname',
                     'firstname',
                     'login',
                     'title',
                     'institution',
                     'department',
                     'street',
                     'zipcode',
                     'city',
                     'country',
                     'sel_country',
                     'email',
                     'second_email',
                     'hobby',
                     'org_units',
                     // begin-patch lok
                     'matriculation',
                     'interests_general',
                     'interests_help_offered',
                     'interests_help_looking'
        );
        // end-patch lok
    }

    public static function _isSearchable(string $a_key) : bool
    {
        return in_array($a_key, ilUserSearchOptions::_getPossibleFields());
    }

    public static function _isEnabled($a_key) : bool
    {
        global $DIC;

        $settings = $DIC->settings();

        // login is always enabled
        if ($a_key == 'login') {
            return true;
        }
        return (bool) $settings->get('search_enabled_' . $a_key);
    }

    public static function _saveStatus(string $a_key, bool $a_enabled) : bool
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        $ilias->setSetting('search_enabled_' . $a_key, (string) $a_enabled);
        return true;
    }

    public static function __appendUserDefinedFields(array $fields, int $counter) : array
    {
        $user_defined_fields = ilUserDefinedFields::_getInstance();

        $fields = [];
        foreach ($user_defined_fields->getSearchableDefinitions() as $definition) {
            $fields[$counter]['values'] = ilUserSearchOptions::__prepareValues($definition['field_values']);
            $fields[$counter]['lang'] = $definition['field_name'];
            $fields[$counter]['db'] = $definition['field_id'];

            switch ($definition['field_type']) {
                case UDF_TYPE_TEXT:
                    $fields[$counter]['type'] = self::FIELD_TYPE_UDF_TEXT;
                    break;

                case UDF_TYPE_SELECT:
                    $fields[$counter]['type'] = self::FIELD_TYPE_UDF_SELECT;
                    break;
                
                case UDF_TYPE_WYSIWYG:
                    $fields[$counter]['type'] = self::FIELD_TYPE_UDF_WYSIWYG;
                    break;

                default:
                    throw new \Exception('unsupported udf type');
            }
            ++$counter;
        }
        return $fields;
    }

    public static function __prepareValues(array $a_values) : array
    {
        global $DIC;

        $lng = $DIC->language();

        $new_values = array(0 => $lng->txt('please_choose'));
        foreach ($a_values as $value) {
            $new_values[$value] = $value;
        }
        return $new_values;
    }
}
