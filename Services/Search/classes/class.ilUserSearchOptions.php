<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public const FIELD_TYPE_UDF_UNDEFINED = 0;
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
                    // do not throw: udf plugin support
                    $fields[$counter]['type'] = $definition['field_type'];
                    break;
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
