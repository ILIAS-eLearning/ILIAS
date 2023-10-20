<?php

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

declare(strict_types=1);

/**
 * @defgroup
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesPrivacySecurity
 */
class ilExportFieldsInfo
{
    private static array $instances = [];

    private ilSetting $settings;
    private ilLanguage $lng;
    private string $obj_type = '';
    private array $possible_fields = array();

    /**
     * Private Singleton Constructor. Use getInstance
     * @access private
     */
    private function __construct(string $a_type)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->obj_type = $a_type;

        $this->read();
    }

    /**
     * Get Singleton Instance
     */
    public static function _getInstanceByType(string $a_type): ilExportFieldsInfo
    {
        if (!isset(self::$instances[$a_type])) {
            self::$instances[$a_type] = new self($a_type);
        }
        return self::$instances[$a_type];
    }

    public function getType(): string
    {
        return $this->obj_type;
    }

    /**
     * Check if field is exportable
     */
    public function isExportable($a_field_name): bool
    {
        return array_key_exists($a_field_name, $this->possible_fields);
    }

    /**
     * Get informations (exportable) about user data profile fields
     * @access public
     */
    public function getFieldsInfo(): array
    {
        return $this->possible_fields;
    }

    /**
     * Get Exportable Fields
     */
    public function getExportableFields(): array
    {
        $fields = [];
        foreach ($this->possible_fields as $field => $exportable) {
            if ($exportable) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    /**
     * Get selectable fields
     */
    public function getSelectableFieldsInfo(?int $a_obj_id = null): array
    {
        global $DIC;

        $user = $DIC->user();

        $fields = [];
        foreach ($this->getExportableFields() as $field) {
            switch ($field) {
                case 'lastname':
                case 'firstname':
                    break;

                case 'username':
                    $fields['login']['txt'] = $this->lng->txt('login');
                    $fields['login']['default'] = 1;
                    break;

                default:
                    // #18795
                    $caption = ($field == "title")
                        ? "person_title"
                        : $field;
                    $fields[$field]['txt'] = $this->lng->txt($caption);
                    $fields[$field]['default'] = 0;
                    break;
            }
        }

        if ($a_obj_id && ilBookingEntry::hasObjectBookingEntries($a_obj_id, $user->getId())) {
            $this->lng->loadLanguageModule('dateplaner');
            $fields['consultation_hour']['txt'] = $this->lng->txt('cal_ch_field_ch');
            $fields['consultation_hour']['default'] = 0;
        }

        switch ($this->getType()) {
            case 'crs':
                $udf = ilUserDefinedFields::_getInstance()->getCourseExportableFields();
                break;
            case 'grp':
                $udf = ilUserDefinedFields::_getInstance()->getGroupExportableFields();
                break;
            case 'prg':
                $udf = ilUserDefinedFields::_getInstance()->getProgrammeExportableFields();
                break;
        }

        if ($udf) {
            foreach ($udf as $field_id => $field) {
                $fields['udf_' . $field_id]['txt'] = $field['field_name'];
                $fields['udf_' . $field_id]['default'] = 0;
            }
        }

        if ($a_obj_id) {
            $cdf = ilCourseDefinedFieldDefinition::_getFields($a_obj_id);
            foreach ($cdf as $def) {
                $fields['odf_' . $def->getId()]['txt'] = $def->getName();
                $fields['odf_' . $def->getId()]['default'] = 0;
            }
            if (count($cdf)) {
                // add last edit
                $fields['odf_last_update']['txt'] = $this->lng->txt($this->getType() . '_cdf_tbl_last_edit');
                $fields['odf_last_update']['default'] = 0;
            }
        }
        return $fields;
    }

    /**
     * Get exportable fields as info string
     * @return string info page string
     */
    public function exportableFieldsToInfoString(): string
    {
        $fields = [];
        foreach ($this->getExportableFields() as $field) {
            $fields[] = $this->lng->txt($field);
        }
        return implode('<br />', $fields);
    }

    /**
     * Read info about exportable fields
     */
    private function read(): void
    {
        $profile = new ilUserProfile();
        $profile->skipGroup('settings');

        $field_prefix = null;
        $field_part_limit = 5;
        $export_hide = null;
        $export_fix_val = null;
        $type = $this->getType();

        $type_vals = [
            'crs' => ['course_export_hide', 'course_export_fix_value', 'usr_settings_course_export_'],
            'grp' => ['group_export_hide', 'group_export_fix_value', 'usr_settings_group_export_'],
            'prg' => ['prg_export_hide', 'prg_export_fix_value', 'usr_settings_prg_export_']
        ];
        if (array_key_exists($type, $type_vals)) {
            list($export_hide, $export_fix_val, $field_prefix) = $type_vals[$type];
        }

        foreach ($profile->getStandardFields() as $key => $data) {
            if (!array_key_exists($export_hide, $data) || !$data[$export_hide]) {
                if (isset($data[$export_fix_val]) and $data[$export_fix_val]) {
                    $this->possible_fields[$key] = $data[$export_fix_val];
                } else {
                    $this->possible_fields[$key] = 0;
                }
            }
        }

        $settings_all = $this->settings->getAll();
        foreach ($settings_all as $key => $value) {
            if ($field_prefix && stristr($key, $field_prefix) and $value) {
                // added limit for mantis 11096
                $field_parts = explode('_', $key, $field_part_limit);
                $field = $field_parts[count($field_parts) - 1];
                if (array_key_exists($field, $this->possible_fields)) {
                    $this->possible_fields[$field] = 1;
                }
            }
        }
    }

    /**
     * sort Exports fields User for Name Presentation Guideline
     */
    public function sortExportFields(): void
    {
        $start_order = array("lastname" => array(), "firstname" => array(), "username" => array());

        foreach ($start_order as $key => $value) {
            if (isset($this->possible_fields[$key])) {
                $start_order[$key] = $this->possible_fields[$key];
                unset($this->possible_fields[$key]);
            } else {
                unset($start_order[$key]);
            }
        }

        if (count($start_order) > 0) {
            $this->possible_fields = array_merge($start_order, $this->possible_fields);
        }
    }
}
