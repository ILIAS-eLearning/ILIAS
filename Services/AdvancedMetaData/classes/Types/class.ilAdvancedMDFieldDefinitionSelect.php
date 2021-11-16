<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
 * AMD field type select
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelect extends ilAdvancedMDFieldDefinition
{
    protected $options = [];
    protected $confirm_objects = [];
    protected $confirm_objects_values = [];
    protected $confirmed_objects; // [array]

    protected $option_translations = [];

    const REMOVE_ACTION_ID = "-iladvmdrm-";
    
    
    //
    // generic types
    //
    
    public function getType()
    {
        return self::TYPE_SELECT;
    }
    
    // search
    public function getSearchQueryParserValue(ilADTSearchBridge $search_bridge)
    {
        return $search_bridge->getADT()->getSelection();
    }


    
    //
    // ADT
    //
    
    protected function initADTDefinition()
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
        $def->setNumeric(false);

        $options = $this->getOptions();
        $translated_options = [];
        if (isset($this->getOptionTranslations()[$this->language])) {
            $translated_options = $this->getOptionTranslations()[$this->language];
        }
        $def->setOptions(array_replace($options, $translated_options));
        return $def;
    }
    
    
    //
    // properties
    //
    
    /**
     * Set options
     *
     * @param array $a_values
     */
    public function setOptions(array $a_values = null)
    {
        if ($a_values !== null) {
            foreach ($a_values as $idx => $value) {
                $a_values[$idx] = trim($value);
                if (!$a_values[$idx]) {
                    unset($a_values[$idx]);
                }
            }
            $a_values = array_unique($a_values);
            // sort($a_values);
        }
        $this->options = $a_values;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionTranslations()
    {
        return $this->option_translations;
    }

    /**
     * @param string $language
     */
    public function getOptionTranslation(string $language)
    {
        if (isset($this->getOptionTranslations()[$language])) {
            return $this->getOptionTranslations()[$language];
        }
        return [];
    }

    /**
     * @param array $translations
     */
    public function setOptionTranslations(array $translations)
    {
        $this->option_translations = $translations;
    }

    /**
     * @param array  $translations
     * @param string $language
     */
    public function setOptionTranslationsForLanguage(array  $translations, string $language)
    {
        $this->option_translations[$language] = $translations;
    }

    /**
     * @param array $a_def
     */
    protected function importFieldDefinition(array $a_def)
    {
        // options (field_values from adv_mdf_field_definitions are not used)
        $this->setOptions([]);
    }

    /**
     * @return array
     */
    protected function getFieldDefinition()
    {
        return [];
    }
    
    public function getFieldDefinitionForTableGUI(string $content_language)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (strlen($content_language)) {
            $options = $this->getOptionTranslation($content_language);
        } else {
            $options = $this->getOptions();
        }
        return [
            $lng->txt("meta_advmd_select_options") => implode(",", $options)
        ];
    }

    /**
     * Add input elements to definition form
     * @param ilPropertyFormGUI $a_form
     * @param bool              $a_disabled
     * @param string            $language
     * @throws ilFormException
     */
    protected function addCustomFieldToDefinitionForm(ilPropertyFormGUI $a_form, $a_disabled = false, string $language = '')
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (!$this->useDefaultLanguageMode($language)) {
            return $this->addCustomFieldToDefinitionFormInTranslationMode($a_form, $a_disabled, $language);
        }

        // if not the default language is chosen => no add/remove; no sorting
        $field = new ilTextInputGUI($lng->txt("meta_advmd_select_options"), "opts");
        $field->setRequired(true);
        $field->setMulti(true, true);
        $field->setMaxLength(255); // :TODO:
        $a_form->addItem($field);
        
        $options = $this->getOptions();
        if ($options) {
            $field->setMultiValues($options);
            $field->setValue(array_shift($options));
        }
        
        if ($a_disabled) {
            $field->setDisabled(true);
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param bool              $disabled
     * @param string            $language
     */
    protected function addCustomFieldToDefinitionFormInTranslationMode(ilPropertyFormGUI $form, bool $disabled, string $language = '')
    {
        global $DIC;

        $lng = $DIC->language();

        $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->record_id)->getDefaultLanguage();

        $translation = $this->getOptionTranslation($language);

        $first = true;
        foreach ($this->getOptions() as $index => $option)
        {
            $title = '';
            if ($first) {
                $title = $lng->txt("meta_advmd_select_options");
            }
            $text = new ilTextInputGUI($title, 'opts__' . $language . '__' . (string) $index);
            if (isset($translation[$index])) {
                $text->setValue($translation[$index]);
            };
            $text->setInfo($default_language . ': ' . $option);
            $text->setMaxLength(255);
            $text->setRequired(true);

            $first = false;
            $form->addItem($text);
        }
    }
    
    /**
     * Process custom post values from definition form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function buildConfirmedObjects(ilPropertyFormGUI $a_form)
    {
        // #15719
        $recipes = $a_form->getInput("conf_det");
        if (is_array($recipes[$this->getFieldId()])) {
            $recipes = $recipes[$this->getFieldId()];
            $sum = $a_form->getInput("conf_det_act");
            $sum = $sum[$this->getFieldId()];
            $sgl = $a_form->getInput("conf");
            $sgl = $sgl[$this->getFieldId()];
                        
            $res = array();
            foreach ($recipes as $old_option => $recipe) {
                $sum_act = $sum[$old_option];
                $sgl_act = $sgl[$old_option];
                
                if ($recipe == "sum") {
                    // #18885
                    if (!$sum_act) {
                        return;
                    }
                    
                    foreach (array_keys($sgl_act) as $obj_idx) {
                        if ($sum_act == self::REMOVE_ACTION_ID) {
                            $sum_act = "";
                        }
                        if (substr($sum_act, 0, 4) == 'idx_') {
                            $parts = explode('_', $sum_act);
                            $sum_act = $parts[1];
                        }
                        $res[$old_option][$obj_idx] = $sum_act;
                    }
                } else {
                    // #18885
                    foreach ($sgl_act as $sgl_index => $sgl_item) {
                        if (!$sgl_item) {
                            return;
                        } elseif ($sgl_item == self::REMOVE_ACTION_ID) {
                            $sgl_act[$sgl_index] = "";
                        }
                        if (substr($sgl_item, 0, 4) == 'idx_') {
                            $parts = explode('_', $sgl_item);
                            $sgl_act[$sgl_index] = $parts[1];
                        }
                    }
                    
                    $res[$old_option] = $sgl_act;
                }
            }
            return $res;
        }
    }

    /**
     * Import custom post values from definition form
     * @param ilPropertyFormGUI $a_form
     * @param string            $language
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '')
    {
        if (!$this->useDefaultLanguageMode($language)) {
            return $this->importTranslatedFormPostValues($a_form, $language);
        }

        if (!strlen($language)) {
            $language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();
        }

        $old = $this->getOptionTranslation($language);
        $new = $a_form->getInput("opts");
        

        $missing = array_diff_assoc($old, $new);


        if (sizeof($missing)) {
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);

            if (!is_array($this->confirmed_objects)) {
                $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($this->getADTDefinition(), false, true);
                foreach ($missing as $missing_idx => $missing_value) {
                    $in_use = $this->findBySingleValue($search, $missing_idx);
                    if (is_array($in_use)) {
                    foreach ($in_use as $item) {
                            $this->confirm_objects[$missing_idx][] = $item;
                            $this->confirm_objects_values[$missing_idx] = $old[$missing_idx];
                    }
                }

            }
        }
        }
        $this->old_options = $old;
        $this->setOptionTranslationsForLanguage($new, $language);
    }

    protected function findBySingleValue(ilADTEnumSearchBridgeMulti $a_search, $a_value)
    {
        $res = array();
        $a_search->getADT()->setSelections((array) $a_value);
        $condition = $a_search->getSQLCondition('value_index');

        $in_use = ilADTActiveRecordByType::find(
            "adv_md_values",
            "Enum",
            $this->getFieldId(),
            $condition
        );
        if ($in_use) {
            foreach ($in_use as $item) {
                $res[] = array($item["obj_id"], $item["sub_type"], $item["sub_id"], $item["value_index"]);
            }
        }

        return $res;
    }


    /**
     * @param ilPropertyFormGUI $form
     * @param string            $language
     */
    protected function importTranslatedFormPostValues(ilPropertyFormGUI $form, string $language)
    {
        $translated_options = [];
        foreach ($this->getOptions() as $idx => $value) {
            $value = $form->getInput('opts__' . $language . '__' . (string) $idx);
            $translated_options[] = trim($value);
        }
        $translations = $this->getOptionTranslations();
        $translations[$language] = $translated_options;
        $this->setOptionTranslations($translations);
    }
    
    public function importDefinitionFormPostValuesNeedsConfirmation()
    {
        return is_array($this->confirm_objects) && count($this->confirm_objects) > 0;
    }
    
    public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];
                
        $a_form->getItemByPostVar("opts")->setDisabled(true);
        if (is_array($this->confirm_objects) && count($this->confirm_objects) > 0) {
            $new_options = $a_form->getInput("opts");
            
            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
            $a_form->addItem($sec);
                                    
            foreach ($this->confirm_objects as $old_option => $items) {
                $old_option_value = $this->confirm_objects_values[$old_option];
                $details = new ilRadioGroupInputGUI($lng->txt("md_adv_confirm_definition_select_option") . ': "' . $old_option_value . '"', "conf_det[" . $this->getFieldId() . "][" . $old_option . "]");
                $details->setRequired(true);
                $details->setValue("sum");
                $a_form->addItem($details);
                                
                // automatic reload does not work
                if (isset($_POST["conf_det"][$this->getFieldId()][$old_option])) {
                    $details->setValue($_POST["conf_det"][$this->getFieldId()][$old_option]);
                }
                
                $sum = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_all"), "sum");
                $details->addOption($sum);
                
                $sel = new ilSelectInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option_all_action"),
                    "conf_det_act[" . $this->getFieldId() . "][" . $old_option . "]"
                );
                $sel->setRequired(true);
                $options = array(
                    "" => $lng->txt("please_select"),
                    self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                );
                foreach ($new_options as $new_option_index => $new_option) {
                    $options['idx_' . $new_option_index] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
                }
                $sel->setOptions($options);
                $sum->addSubItem($sel);
                
                // automatic reload does not work
                if (isset($_POST["conf_det_act"][$this->getFieldId()][$old_option])) {
                    if ($_POST["conf_det_act"][$this->getFieldId()][$old_option]) {
                        $sel->setValue($_POST["conf_det_act"][$this->getFieldId()][$old_option]);
                    } elseif ($_POST["conf_det"][$this->getFieldId()][$old_option] == "sum") {
                        $sel->setAlert($lng->txt("msg_input_is_required"));
                        ilUtil::sendFailure($lng->txt("form_input_not_valid"));
                    }
                }
                $single = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_single"), "sgl");
                $details->addOption($single);
                foreach ($items as $item) {
                    $obj_id = $item[0];
                    $sub_type = $item[1];
                    $sub_id = $item[2];
                    
                    $item_id = $obj_id . "_" . $sub_type . "_" . $sub_id;
                    
                    $type = ilObject::_lookupType($obj_id);
                    $type_title = $lng->txt("obj_" . $type);
                    $title = ' "' . ilObject::_lookupTitle($obj_id) . '"';
                    
                    if ($sub_id) {
                        $class = "ilObj" . $objDefinition->getClassName($type);
                        $class_path = $objDefinition->getLocation($type);
                        include_once $class_path . "/class." . $class . ".php";
                        $ints = class_implements($class);
                        if (isset($ints["ilAdvancedMetaDataSubItems"])) {
                            $sub_title = $class::getAdvMDSubItemTitle($obj_id, $sub_type, $sub_id);
                            if ($sub_title) {
                                $title .= ' (' . $sub_title . ')';
                            }
                        }
                    }
                    
                    $sel = new ilSelectInputGUI(
                        $type_title . ' ' . $title,
                        "conf[" . $this->getFieldId() . "][" . $old_option . "][" . $item_id . "]"
                    );
                    $sel->setRequired(true);
                    $options = array(
                        "" => $lng->txt("please_select"),
                        self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                    );
                    foreach ($new_options as $new_option_index => $new_option) {
                        $options['idx_' . $new_option_index] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
                    }
                    $sel->setOptions($options);
                    
                    // automatic reload does not work
                    if (isset($_POST["conf"][$this->getFieldId()][$old_option][$item_id])) {
                        if ($_POST["conf"][$this->getFieldId()][$old_option][$item_id]) {
                            $sel->setValue($_POST["conf"][$this->getFieldId()][$old_option][$item_id]);
                        } elseif ($_POST["conf_det"][$this->getFieldId()][$old_option] == "sgl") {
                            $sel->setAlert($lng->txt("msg_input_is_required"));
                            ilUtil::sendFailure($lng->txt("form_input_not_valid"));
                        }
                    }
                    
                    $single->addSubItem($sel);
                }
            }
        }
    }
    
    public function delete()
    {
        $this->deleteOptionTranslations();
        parent::delete();
    }

    public function save($a_keep_pos = false)
    {
        parent::save($a_keep_pos);
        $this->saveOptionTranslations();
    }

    /**
     *
     */
    protected function deleteOptionTranslations()
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'delete from adv_mdf_enum ' .
            'where field_id = ' . $db->quote($this->getFieldId(), ilDBConstants::T_INTEGER);
        $db->manipulate($query);
    }

    protected function updateOptionTranslations()
    {
        $this->deleteOptionTranslations();
        $this->saveOptionTranslations();
    }

    protected function saveOptionTranslations()
    {
        global $DIC;

        $db = $DIC->database();

        foreach ($this->getOptionTranslations() as $lang_key => $options) {
            foreach ($options as $idx => $option) {
                $query = 'insert into adv_mdf_enum (field_id, lang_code, idx, value )' .
                    'values (  ' .
                    $db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ', ' .
                    $db->quote($lang_key, ilDBConstants::T_TEXT) . ', ' .
                    $db->quote($idx, ilDBConstants::T_INTEGER) . ', ' .
                    $db->quote($option, ilDBConstants::T_TEXT).
                    ')' ;
                $db->manipulate($query);
            }
        }
    }
    
    
    //
    // definition CRUD
    //
    
    public function update()
    {
        if (is_array($this->confirmed_objects) && count($this->confirmed_objects) > 0) {
            // we need the "old" options for the search
            $def = $this->getADTDefinition();
            $def = clone($def);
            $def->setOptions($this->old_options);
            $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def, false, true);
            ilADTFactory::initActiveRecordByType();

            foreach ($this->confirmed_objects as $old_option => $item_ids) {
                // get complete old values
                $old_values = array();
                foreach ($this->findBySingleValue($search, $old_option) as $item) {
                    $old_values[$item[0] . "_" . $item[1] . "_" . $item[2]] = $item[3];
                }


                foreach ($item_ids as $item => $new_option) {
                    $parts = explode("_", $item);
                    $obj_id = $parts[0];
                    $sub_type = $parts[1];
                    $sub_id = $parts[2];

                    // update existing value (with changed option)
                    if (isset($old_values[$item])) {
                        $old_value = $old_values[$item];
                        // find changed option in old value
                        //$old_value = explode(ilADTMultiEnumDBBridge::SEPARATOR, $old_values[$item]);
                        // remove separators
                        //array_shift($old_value);
                        //array_pop($old_value);


                        //$old_idx = array_keys($old_value, $old_option);
                        $old_idx = $old_value;
                        if (isset($old_idx)) {
                    
                        $primary = array(
                            "obj_id" => array("integer", $obj_id),
                            "sub_type" => array("text", $sub_type),
                            "sub_id" => array("integer", $sub_id),
                            "field_id" => array("integer", $this->getFieldId())
                        );

                            $index_old = array_merge(
                                $primary,
                                [
                                    'value_index' => [ilDBConstants::T_INTEGER, $old_idx]
                                ]
                        );
                            $index_new = array_merge(
                                $primary,
                                [
                                    'value_index' => [ilDBConstants::T_INTEGER, $new_option]
                                ]
                            );
                            ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $index_old, 'MultiEnum');

                            if (is_numeric($new_option)) {
                                ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $index_new, 'MultiEnum');
                                ilADTActiveRecordByType::create('adv_md_values', $index_new, 'MultiEnum');
                            } else {
                            }
                        }
                    }
                    
                    if ($sub_type == "wpg") {
                        // #15763 - adapt advmd page lists
                        include_once "Modules/Wiki/classes/class.ilPCAMDPageList.php";
                        ilPCAMDPageList::migrateField($obj_id, $this->getFieldId(), $old_option, $new_option, true);
                    }
                }
            }

            $this->confirmed_objects = array();
        }

        parent::update();
        $this->updateOptionTranslations();
    }
    
    
    //
    // export
    //
    
    protected function addPropertiesToXML(ilXmlWriter $a_writer)
    {
        foreach ($this->getOptions() as $value) {
            $a_writer->xmlElement('FieldValue', null, $value);
        }
        foreach ($this->getOptionTranslations() as $lang_key => $translations) {
            foreach ((array) $translations as $value) {
                $a_writer->xmlElement('FieldValue', ['id' => $lang_key], $value);
            }
        }
    }
    
    /**
     * @param string $a_key
     * @param string $a_value
     */
    public function importXMLProperty($a_key, $a_value)
    {
        if (!$a_key) {
        $this->options[] = $a_value;
        } else {
            $this->option_translations[$a_key][] = $a_value;
        }
    }
    
    
    //
    // import/export
    //
    
    public function getValueForXML(ilADT $element)
    {
        return $element->getSelection();
    }
    
    public function importValueFromXML($a_cdata)
    {
        $this->getADT()->setSelection($a_cdata);
    }
    
    
    //
    // presentation
    //
    
    public function prepareElementForEditor(ilADTFormBridge $a_enum)
    {
        assert($a_enum instanceof ilADTEnumFormBridge);
        
        $a_enum->setAutoSort(false);
    }

    protected function import(array $db_data)
    {
        global $DIC;

        parent::import($db_data);

        $db = $DIC->database();
        $query = 'select * from adv_mdf_enum ' .
            'where field_id = ' . $db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'order by idx';
        $res = $db->query($query);
        $options = [];
        $default = [];
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->lang_code == $record->getDefaultLanguage()) {
                $default[$row->idx] = $row->value;
            }
            $options[$row->lang_code][$row->idx] = $row->value;
        }
        $this->setOptions($default);
        $this->setOptionTranslations($options);
    }

    /**
     * @param int $a_new_record_id
     * @return ilAdvancedMDFieldDefinition
     */
    public function _clone($a_new_record_id)
    {
        global $DIC;

        $db = $DIC->database();

        $obj = parent::_clone($a_new_record_id);
        $query = 'select * from adv_mdf_enum ' .
            'where field_id = ' . $db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'order by idx';
        $res = $db->query($query);
        $options = [];
        $default = [];
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->lang_code == $record->getDefaultLanguage()) {
                $default[$row->idx] = $row->value;
            }
            $options[$row->lang_code][$row->idx] = $row->value;
        }
        $obj->setOptions($default);
        $obj->setOptionTranslations($options);
        $obj->update();
        return $obj;
    }
}
