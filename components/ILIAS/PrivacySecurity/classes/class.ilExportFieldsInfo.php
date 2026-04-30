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

use ILIAS\User\Profile\Profile;
use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\Field as ProfileField;

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
    private Profile $profile;
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
        $this->profile = $DIC['user']->getProfile();
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
        $profile = $DIC['user']->getProfile();

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

        $context = Context::buildFromObjectType($this->getType());
        $udf = $context === null ?
            [] : $profile->getVisibleUserDefinedFields($context);
        if ($udf !== []) {
            foreach ($udf as $field_id => $field) {
                $fields['udf_' . $field_id]['txt'] = $field->getLabel($this->lng);
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
        $type = $this->getType();
        $this->possible_fields = array_reduce(
            $this->profile->getFields(),
            function (array $c, ProfileField $v) use ($type): array {
                if ($v->isCustom()) {
                    return $c;
                }
                if ($type === 'crs') {
                    $c[$v->getIdentifier()] = $v->isVisibleInCourses();
                }

                if ($type === 'grp') {
                    $c[$v->getIdentifier()] = $v->isVisibleInGroups();
                }

                if ($type === 'prg') {
                    $c[$v->getIdentifier()] = $v->isVisibleInStudyProgrammes();
                }

                return $c;
            },
            $this->possible_fields
        );
    }

    /**
     * sort Exports fields User for Name Presentation Guideline
     */
    public function sortExportFields(): void
    {
        $start_order = ['lastname' => [], 'firstname' => [], 'username' => []];

        foreach (array_keys($start_order) as $key) {
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
