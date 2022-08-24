<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Field definitions of advanced meta data search
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchFields
{
    public const ONLINE_QUERY = 1;
    public const OFFLINE_QUERY = 2;

    private static ?ilLuceneAdvancedSearchFields $instance = null;
    private ilLuceneAdvancedSearchSettings $settings;

    protected ilLanguage $lng;
    protected ilObjUser $user;

    private static array $fields = [];
    private array $active_fields = [];

    private static array $sections = [];
    private array $active_sections = [];


    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->user = $DIC->user();

        $this->settings = ilLuceneAdvancedSearchSettings::getInstance();

        $this->readFields();
        $this->readSections();
    }

    public static function getInstance(): ilLuceneAdvancedSearchFields
    {
        if (self::$instance instanceof ilLuceneAdvancedSearchFields) {
            return self::$instance;
        }
        return self::$instance = new ilLuceneAdvancedSearchFields();
    }

    /**
     * Return an array of all meta data fields
     * @return array<string, string>
     */
    public static function getFields(): array
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule('meta');

        $fields['lom_content'] = $lng->txt('content');

        if (ilSearchSettings::getInstance()->enabledLucene()) {
            $fields['general_offline'] = $lng->txt('lucene_offline_filter');
        }
        //'lom_type'					= $lng->txt('type');
        $fields['lom_language'] = $lng->txt('language');
        $fields['lom_keyword'] = $lng->txt('meta_keyword');
        $fields['lom_coverage'] = $lng->txt('meta_coverage');
        $fields['lom_structure'] = $lng->txt('meta_structure');
        $fields['lom_status'] = $lng->txt('meta_status');
        $fields['lom_version'] = $lng->txt('meta_version');
        $fields['lom_contribute'] = $lng->txt('meta_contribute');
        $fields['lom_format'] = $lng->txt('meta_format');
        $fields['lom_operating_system'] = $lng->txt('meta_operating_system');
        $fields['lom_browser'] = $lng->txt('meta_browser');
        $fields['lom_interactivity'] = $lng->txt('meta_interactivity_type');
        $fields['lom_resource'] = $lng->txt('meta_learning_resource_type');
        $fields['lom_level'] = $lng->txt('meta_interactivity_level');
        $fields['lom_density'] = $lng->txt('meta_semantic_density');
        $fields['lom_user_role'] = $lng->txt('meta_intended_end_user_role');
        $fields['lom_context'] = $lng->txt('meta_context');
        $fields['lom_difficulty'] = $lng->txt('meta_difficulty');
        $fields['lom_costs'] = $lng->txt('meta_cost');
        $fields['lom_copyright'] = $lng->txt('meta_copyright_and_other_restrictions');
        $fields['lom_purpose'] = $lng->txt('meta_purpose');
        $fields['lom_taxon'] = $lng->txt('meta_taxon');

        // Append all advanced meta data fields
        foreach (ilAdvancedMDRecord::_getRecords() as $record) {
            if ($record->getParentObject() > 0) {
                if (!ilObject::_hasUntrashedReference($record->getParentObject())) {
                    continue;
                }
            }

            foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record->getRecordId(), true) as $def) {
                $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record->getRecordId());
                $fields['adv_' . $def->getFieldId()] = $field_translations->getTitleForLanguage($def->getFieldId(), $lng->getLangKey());
            }
        }

        return $fields;
    }

    /**
     * Get all active fields
     * @return array<string, string>
     */
    public function getActiveFields(): array
    {
        return $this->active_fields;
    }

    public function getActiveSections(): array
    {
        return $this->active_sections;
    }

    /**
     * @param string | array    $a_query
     */
    public function getFormElement($a_query, string $a_field_name, ilPropertyFormGUI $a_form): ?ilFormPropertyGUI
    {
        $a_post_name = 'query[' . $a_field_name . ']';

        if (!is_array($a_query)) {
            $a_query = array();
        }

        switch ($a_field_name) {
            case 'general_offline':
                $offline_options = array(
                    '0' => $this->lng->txt('search_any'),
                    self::ONLINE_QUERY => $this->lng->txt('search_option_online'),
                    self::OFFLINE_QUERY => $this->lng->txt('search_option_offline')
                );
                $offline = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $offline->setOptions($offline_options);
                $offline->setValue($a_query['general_offline']);
                return $offline;

            case 'lom_content':
                $text = new ilTextInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $text->setSubmitFormOnEnter(true);
                $text->setValue($a_query['lom_content']);
                $text->setSize(30);
                $text->setMaxLength(255);
                return $text;

            // General
            case 'lom_language':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_language']);
                $select->setOptions((array) ilMDUtilSelect::_getLanguageSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_keyword':
                $text = new ilTextInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $text->setSubmitFormOnEnter(true);
                $text->setValue($a_query['lom_keyword']);
                $text->setSize(30);
                $text->setMaxLength(255);
                return $text;

            case 'lom_coverage':
                $text = new ilTextInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $text->setSubmitFormOnEnter(true);
                $text->setValue($a_query['lom_coverage']);
                $text->setSize(30);
                $text->setMaxLength(255);
                return $text;

            case 'lom_structure':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_structure']);
                $select->setOptions((array) ilMDUtilSelect::_getStructureSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            // Lifecycle
            case 'lom_status':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_status']);
                $select->setOptions((array) ilMDUtilSelect::_getStatusSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_version':
                $text = new ilTextInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $text->setSubmitFormOnEnter(true);
                $text->setValue($a_query['lom_version']);
                $text->setSize(30);
                $text->setMaxLength(255);
                return $text;

            case 'lom_contribute':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], 'query[' . 'lom_role' . ']');
                $select->setValue($a_query['lom_role']);
                $select->setOptions((array) ilMDUtilSelect::_getRoleSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));

                    $text = new ilTextInputGUI($this->lng->txt('meta_entry'), 'query[' . 'lom_role_entry' . ']');
                    $text->setValue($a_query['lom_role_entry']);
                    $text->setSize(30);
                    $text->setMaxLength(255);

                $select->addSubItem($text);
                return $select;

            // Technical
            case 'lom_format':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_format']);
                $select->setOptions((array) ilMDUtilSelect::_getFormatSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_operating_system':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_operating_system']);
                $select->setOptions((array) ilMDUtilSelect::_getOperatingSystemSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_browser':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_browser']);
                $select->setOptions((array) ilMDUtilSelect::_getBrowserSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            // Education
            case 'lom_interactivity':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_interactivity']);
                $select->setOptions((array) ilMDUtilSelect::_getInteractivityTypeSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_resource':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_resource']);
                $select->setOptions((array) ilMDUtilSelect::_getLearningResourceTypeSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_level':
                $range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
                $html = $this->getRangeSelect(
                    $this->lng->txt('from'),
                    (string) ilMDUtilSelect::_getInteractivityLevelSelect(
                        $a_query['lom_level_start'],
                        'query[' . 'lom_level_start' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    ),
                    $this->lng->txt('until'),
                    (string) ilMDUtilSelect::_getInteractivityLevelSelect(
                        $a_query['lom_level_end'],
                        'query[' . 'lom_level_end' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    )
                );
                $range->setHtml($html);
                return $range;

            case 'lom_density':
                $range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
                $html = $this->getRangeSelect(
                    $this->lng->txt('from'),
                    (string) ilMDUtilSelect::_getSemanticDensitySelect(
                        $a_query['lom_density_start'],
                        'query[' . 'lom_density_start' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    ),
                    $this->lng->txt('until'),
                    (string) ilMDUtilSelect::_getSemanticDensitySelect(
                        $a_query['lom_density_end'],
                        'query[' . 'lom_density_end' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    )
                );
                $range->setHtml($html);
                return $range;


            case 'lom_user_role':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_user_role']);
                $select->setOptions((array) ilMDUtilSelect::_getIntendedEndUserRoleSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_context':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_context']);
                $select->setOptions((array) ilMDUtilSelect::_getContextSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_difficulty':
                $range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
                $html = $this->getRangeSelect(
                    $this->lng->txt('from'),
                    (string) ilMDUtilSelect::_getDifficultySelect(
                        $a_query['lom_difficulty_start'],
                        'query[' . 'lom_difficulty_start' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    ),
                    $this->lng->txt('until'),
                    (string) ilMDUtilSelect::_getDifficultySelect(
                        $a_query['lom_difficulty_end'],
                        'query[' . 'lom_difficulty_end' . ']',
                        array(0 => $this->lng->txt('search_any'))
                    )
                );
                $range->setHtml($html);
                return $range;

            // Rights
            case 'lom_costs':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_costs']);
                $select->setOptions((array) ilMDUtilSelect::_getCostsSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_copyright':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_copyright']);
                $select->setOptions((array) ilMDUtilSelect::_getCopyrightAndOtherRestrictionsSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;



            // Classification
            case 'lom_purpose':
                $select = new ilSelectInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $select->setValue($a_query['lom_purpose']);
                $select->setOptions((array) ilMDUtilSelect::_getPurposeSelect(
                    '',
                    $a_field_name,
                    array(0 => $this->lng->txt('search_any')),
                    true
                ));
                return $select;

            case 'lom_taxon':
                $text = new ilTextInputGUI($this->active_fields[$a_field_name], $a_post_name);
                $text->setSubmitFormOnEnter(true);
                $text->setValue($a_query['lom_taxon']);
                $text->setSize(30);
                $text->setMaxLength(255);
                return $text;

            default:
                if (substr($a_field_name, 0, 3) != 'adv') {
                    break;
                }

                // Advanced meta data
                $field_id = substr($a_field_name, 4);
                $field = ilAdvancedMDFieldDefinition::getInstance((int) $field_id);

                $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($field->getADTDefinition(), true, false);
                $field_form->setForm($a_form);
                $field_form->setElementId($a_post_name);
                $field_form->setTitle($this->active_fields[$a_field_name]);
                $field_form->addToForm();

                // #17071 - reload search values
                if (is_array($a_query) &&
                    array_key_exists($a_field_name, $a_query)) {
                    $field_form->importFromPost((array) $a_query);
                    $field_form->validate();
                }
                return null;
        }
        return null;
    }


    /**
     * Called from ilLuceneAdvancedQueryParser
     * Parse a field specific query
     */
    public function parseFieldQuery(string $a_field, string $a_query): string
    {
        switch ($a_field) {
            case 'lom_content':
                return $a_query;

            case 'general_offline':

                switch ($a_query) {
                    case self::OFFLINE_QUERY:
                        return 'offline:1';

                    default:
                        return '-offline:1';

                }

            // General
            // no break
            case 'lom_language':
                return 'lomLanguage:' . $a_query;

            case 'lom_keyword':
                return 'lomKeyword:' . $a_query;

            case 'lom_coverage':
                return 'lomCoverage:' . $a_query;

            case 'lom_structure':
                return 'lomStructure:' . $a_query;

            // Lifecycle
            case 'lom_status':
                return 'lomStatus:' . $a_query;

            case 'lom_version':
                return 'lomVersion:' . $a_query;

            // Begin Contribute
            case 'lom_role':
                return 'lomRole:' . $a_query;

            case 'lom_role_entry':
                return 'lomRoleEntity:' . $a_query;
            // End contribute

            // Technical
            case 'lom_format':
                return 'lomFormat:' . $a_query;

            case 'lom_operating_system':
                return 'lomOS:' . $a_query;

            case 'lom_browser':
                return 'lomBrowser:' . $a_query;

            // Educational
            case 'lom_interactivity':
                return 'lomInteractivity:' . $a_query;

            case 'lom_resource':
                return 'lomResource:' . $a_query;

            case 'lom_level_start':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getInteractivityLevelSelect(0, 'lom_level', array(), true);
                for ($i = $a_query; $i <= count($options); $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomLevel:"' . $options[$i] . '" ');
                }
                return $q_string;

            case 'lom_level_end':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getInteractivityLevelSelect(0, 'lom_level', array(), true);
                for ($i = 1; $i <= $a_query; $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomLevel:"' . $options[$i] . '" ');
                }
                return $q_string;

            case 'lom_density_start':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getSemanticDensitySelect(0, 'lom_density', array(), true);
                for ($i = $a_query; $i <= count($options); $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomDensity:"' . $options[$i] . '" ');
                }
                return $q_string;

            case 'lom_density_end':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getSemanticDensitySelect(0, 'lom_density', array(), true);
                for ($i = 1; $i <= $a_query; $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomDensity:"' . $options[$i] . '" ');
                }
                return $q_string;

            case 'lom_user_role':
                return 'lomUserRole:' . $a_query;

            case 'lom_context':
                return 'lomContext:' . $a_query;

            case 'lom_difficulty_start':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getDifficultySelect(0, 'lom_difficulty', array(), true);
                for ($i = $a_query; $i <= count($options); $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomDifficulty:"' . $options[$i] . '" ');
                }
                return $q_string;

            case 'lom_difficulty_end':
                $q_string = '';
                $options = (array) ilMDUtilSelect::_getDifficultySelect(0, 'lom_difficulty', array(), true);
                for ($i = 1; $i <= $a_query; $i++) {
                    if (strlen($q_string)) {
                        $q_string .= 'OR ';
                    }
                    $q_string .= ('lomDifficulty:"' . $options[$i] . '" ');
                }
                return $q_string;

            // Rights
            case 'lom_costs':
                return 'lomCosts:' . $a_query;

            case 'lom_copyright':
                return 'lomCopyright:' . $a_query;

            // Classification
            case 'lom_purpose':
                return 'lomPurpose:' . $a_query;

            case 'lom_taxon':
                return 'lomTaxon:' . $a_query;

            default:
                if (substr($a_field, 0, 3) != 'adv') {
                    break;
                }

                // Advanced meta data
                $field_id = substr($a_field, 4);
                try {
                    // field might be invalid (cached query)
                    $field = ilAdvancedMDFieldDefinition::getInstance((int) $field_id);
                } catch (Exception $ex) {
                    return '';
                }

                $adv_query = $field->getLuceneSearchString($a_query);
                if ($adv_query) {
                    // #17558
                    if (!is_array($adv_query)) {
                        return 'advancedMetaData_' . $field_id . ': ' . $adv_query;
                    } else {
                        $res = array();
                        foreach ($adv_query as $adv_query_item) {
                            $res[] = 'advancedMetaData_' . $field_id . ': ' . $adv_query_item;
                        }
                        return '(' . implode(' OR ', $res) . ')';
                    }
                }
        }
        return '';
    }


    /**
     * Read active fields
     */
    protected function readFields(): void
    {
        foreach (self::getFields() as $name => $translation) {
            if ($this->settings->isActive($name)) {
                $this->active_fields[$name] = $translation;
            }
        }
    }

    /**
     * Read active sections
     */
    protected function readSections(): void
    {
        foreach ($this->getActiveFields() as $field_name => $translation) {
            switch ($field_name) {
                // Default section
                case 'lom_content':
                    $this->active_sections['default']['fields'][] = 'lom_content';
                    $this->active_sections['default']['name'] = '';
                    break;

                case 'general_offline':
                    $this->active_sections['default']['fields'][] = 'general_offline';
                    $this->active_sections['default']['name'] = '';
                    break;

                case 'lom_type':
                    $this->active_sections['default']['fields'][] = 'lom_type';
                    $this->active_sections['default']['name'] = '';
                    break;

                // General
                case 'lom_language':
                    $this->active_sections['general']['fields'][] = 'lom_language';
                    $this->active_sections['general']['name'] = $this->lng->txt('meta_general');
                    break;
                case 'lom_keyword':
                    $this->active_sections['general']['fields'][] = 'lom_keyword';
                    $this->active_sections['general']['name'] = $this->lng->txt('meta_general');
                    break;
                case 'lom_coverage':
                    $this->active_sections['general']['fields'][] = 'lom_coverage';
                    $this->active_sections['general']['name'] = $this->lng->txt('meta_general');
                    break;
                case 'lom_structure':
                    $this->active_sections['general']['fields'][] = 'lom_structure';
                    $this->active_sections['general']['name'] = $this->lng->txt('meta_general');
                    break;

                // Lifecycle
                case 'lom_status':
                    $this->active_sections['lifecycle']['fields'][] = 'lom_status';
                    $this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
                    break;
                case 'lom_version':
                    $this->active_sections['lifecycle']['fields'][] = 'lom_version';
                    $this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
                    break;
                case 'lom_contribute':
                    $this->active_sections['lifecycle']['fields'][] = 'lom_contribute';
                    $this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
                    break;

                // Technical
                case 'lom_format':
                    $this->active_sections['technical']['fields'][] = 'lom_format';
                    $this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
                    break;
                case 'lom_operating_system':
                    $this->active_sections['technical']['fields'][] = 'lom_operating_system';
                    $this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
                    break;
                case 'lom_browser':
                    $this->active_sections['technical']['fields'][] = 'lom_browser';
                    $this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
                    break;

                // Education
                case 'lom_interactivity':
                    $this->active_sections['education']['fields'][] = 'lom_interactivity';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_resource':
                    $this->active_sections['education']['fields'][] = 'lom_resource';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_level':
                    $this->active_sections['education']['fields'][] = 'lom_level';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_density':
                    $this->active_sections['education']['fields'][] = 'lom_density';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_user_role':
                    $this->active_sections['education']['fields'][] = 'lom_user_role';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_context':
                    $this->active_sections['education']['fields'][] = 'lom_context';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;
                case 'lom_difficulty':
                    $this->active_sections['education']['fields'][] = 'lom_difficulty';
                    $this->active_sections['education']['name'] = $this->lng->txt('meta_education');
                    break;

                // Rights
                case 'lom_costs':
                    $this->active_sections['rights']['fields'][] = 'lom_costs';
                    $this->active_sections['rights']['name'] = $this->lng->txt('meta_rights');
                    break;
                case 'lom_copyright':
                    $this->active_sections['rights']['fields'][] = 'lom_copyright';
                    $this->active_sections['rights']['name'] = $this->lng->txt('meta_rights');
                    break;

                // Classification
                case 'lom_purpose':
                    $this->active_sections['classification']['fields'][] = 'lom_purpose';
                    $this->active_sections['classification']['name'] = $this->lng->txt('meta_classification');
                    break;
                case 'lom_taxon':
                    $this->active_sections['classification']['fields'][] = 'lom_taxon';
                    $this->active_sections['classification']['name'] = $this->lng->txt('meta_classification');
                    break;

                default:
                    if (substr($field_name, 0, 3) != 'adv') {
                        break;
                    }

                    // Advanced meta data
                    $field_id = substr($field_name, 4);
                    $field = ilAdvancedMDFieldDefinition::getInstance((int) $field_id);
                    $record_id = $field->getRecordId();

                    $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);
                    $this->active_sections['adv_record_' . $record_id]['fields'][] = $field_name;
                    $this->active_sections['adv_record_' . $record_id]['name'] = $translations->getTitleForLanguage($this->user->getLanguage());
                    break;
            }
        }
    }

    /**
     * get a range selection
     */
    protected function getRangeSelect(
        string $txt_from,
        string $select_from,
        string $txt_until,
        string $select_until
    ): string {
        $tpl = new ilTemplate('tpl.range_search.html', true, true, 'Services/Search');
        $tpl->setVariable('TXT_FROM', $txt_from);
        $tpl->setVariable('FROM', $select_from);
        $tpl->setVariable('TXT_UPTO', $txt_until);
        $tpl->setVariable('UPTO', $select_until);
        return $tpl->get();
    }
}
