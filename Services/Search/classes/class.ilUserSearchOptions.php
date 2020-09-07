<?php
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
define('FIELD_TYPE_UDF_SELECT', 1);
define('FIELD_TYPE_UDF_TEXT', 2);
define('FIELD_TYPE_SELECT', 3);
define('FIELD_TYPE_TEXT', 4);
// begin-patch lok
define('FIELD_TYPE_MULTI', 5);
// end-patch lok




class ilUserSearchOptions
{
    public $db = null;

    /**
     * Get info of searchable fields for selectable columns in table gui
     * @param bool $a_admin
     * @return array
     */
    public static function getSelectableColumnInfo($a_admin = false)
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

    public static function _getSearchableFieldsInfo($a_admin = false)
    {
        global $DIC;

        $lng = $DIC['lng'];

        // begin-patch lok
        $lng->loadLanguageModule('user');
        // end-patch lok

        $counter = 1;
        foreach (ilUserSearchOptions::_getPossibleFields($a_admin) as $field) {
            // TODO: check enabled
            // DONE
            if ($a_admin == false and !ilUserSearchOptions::_isEnabled($field)) {
                continue;
            }
            $fields[$counter]['values'] = array();
            $fields[$counter]['type'] = FIELD_TYPE_TEXT;
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
                    $fields[$counter]['type'] = FIELD_TYPE_SELECT;
                    $fields[$counter]['values'] = array(
                        0 => $lng->txt('please_choose'),
                        'n' => $lng->txt('gender_n'),
                        'f' => $lng->txt('gender_f'),
                        'm' => $lng->txt('gender_m'),
                    );
                    break;
                
                case 'sel_country':
                    $fields[$counter]['type'] = FIELD_TYPE_SELECT;
                    $fields[$counter]['values'] = array(0 => $lng->txt('please_choose'));
                    
                    // #7843 -- see ilCountrySelectInputGUI
                    $lng->loadLanguageModule('meta');
                    include_once('./Services/Utilities/classes/class.ilCountry.php');
                    foreach (ilCountry::getCountryCodes() as $c) {
                        $fields[$counter]['values'][$c] = $lng->txt('meta_c_' . $c);
                    }
                    asort($fields[$counter]['values']);
                    break;
                    
                case 'org_units':
                    $fields[$counter]['type'] = FIELD_TYPE_SELECT;

                    include_once './Modules/OrgUnit/classes/PathStorage/class.ilOrgUnitPathStorage.php';
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
                    $fields[$counter]['type'] = FIELD_TYPE_MULTI;
                    break;
                // end-patch lok
                    
                    
                    
                                        
                /*
                case 'active':
                    $fields[$counter]['type'] = FIELD_TYPE_SELECT;
                    $fields[$counter]['values'] = array(-1 => $lng->txt('please_choose'),
                                                    '1' => $lng->txt('active'),
                                                    '0' => $lng->txt('inactive'));

                    break;
                */
            }
            
            ++$counter;
        }
        $fields = ilUserSearchOptions::__appendUserDefinedFields($fields, $counter);

        return $fields ? $fields : array();
    }

    public static function _getPossibleFields($a_admin = false)
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

    public static function _isSearchable($a_key)
    {
        return in_array($a_key, ilUserSearchOptions::_getPossibleFields());
    }

    public static function _isEnabled($a_key)
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        // login is always enabled
        if ($a_key == 'login') {
            return true;
        }

        return (bool) $ilias->getSetting('search_enabled_' . $a_key);
    }

    public static function _saveStatus($a_key, $a_enabled)
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        $ilias->setSetting('search_enabled_' . $a_key, (int) $a_enabled);
        return true;
    }

    public static function __appendUserDefinedFields($fields, $counter)
    {
        include_once './Services/User/classes/class.ilUserDefinedFields.php';

        $user_defined_fields = ilUserDefinedFields::_getInstance();
        
        foreach ($user_defined_fields->getSearchableDefinitions() as $definition) {
            $fields[$counter]['values'] = ilUserSearchOptions::__prepareValues($definition['field_values']);
            $fields[$counter]['lang'] = $definition['field_name'];
            $fields[$counter]['db'] = $definition['field_id'];

            switch ($definition['field_type']) {
                case UDF_TYPE_TEXT:
                    $fields[$counter]['type'] = FIELD_TYPE_UDF_TEXT;
                    break;

                case UDF_TYPE_SELECT:
                    $fields[$counter]['type'] = FIELD_TYPE_UDF_SELECT;
                    break;
            }
            ++$counter;
        }
        return $fields ? $fields : array();
    }

    public static function __prepareValues($a_values)
    {
        global $DIC;

        $lng = $DIC['lng'];

        $new_values = array(0 => $lng->txt('please_choose'));
        foreach ($a_values as $value) {
            $new_values[$value] = $value;
        }
        return $new_values ? $new_values : array();
    }
}
