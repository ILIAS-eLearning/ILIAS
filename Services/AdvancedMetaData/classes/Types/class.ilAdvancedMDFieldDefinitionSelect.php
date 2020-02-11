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
    protected $confirmed_objects; // [array]
    
    const REMOVE_ACTION_ID = "-iladvmdrm-";
    
    
    //
    // generic types
    //
    
    public function getType()
    {
        return self::TYPE_SELECT;
    }
    
    
    //
    // ADT
    //
    
    protected function initADTDefinition()
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
        $def->setNumeric(false);
        
        $options = $this->getOptions();
        $def->setOptions(array_combine($options, $options));
        
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
    
    
    //
    // definition (NOT ADT-based)
    //
    
    protected function importFieldDefinition(array $a_def)
    {
        $this->setOptions($a_def);
    }
    
    protected function getFieldDefinition()
    {
        return $this->options;
    }
    
    public function getFieldDefinitionForTableGUI()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return array($lng->txt("meta_advmd_select_options") => implode(",", $this->getOptions()));
    }
    
    /**
     * Add input elements to definition form
     *
     * @param ilPropertyFormGUI $a_form
     * @param bool $a_disabled
     */
    public function addCustomFieldToDefinitionForm(ilPropertyFormGUI $a_form, $a_disabled = false)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
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
                    }
                    
                    $res[$old_option] = $sgl_act;
                }
            }
            
            return $res;
        }
    }
    
    /**
     * Import custom post values from definition form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form)
    {
        $old = $this->getOptions();
        $new = $a_form->getInput("opts");
        
        $missing = array_diff($old, $new);
        if (sizeof($missing)) {
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);
            if (!is_array($this->confirmed_objects)) {
                ilADTFactory::initActiveRecordByType();
                $primary = array(
                    "field_id" => array("integer", $this->getFieldId()),
                    ilADTActiveRecordByType::SINGLE_COLUMN_NAME => array("text", $missing)
                );
                $in_use = ilADTActiveRecordByType::readByPrimary("adv_md_values", $primary, "Enum");
                if ($in_use) {
                    $this->confirm_objects = [];
                    foreach ($in_use as $item) {
                        $this->confirm_objects[$item[ilADTActiveRecordByType::SINGLE_COLUMN_NAME]][] = array($item["obj_id"], $item["sub_type"], $item["sub_id"]);
                    }
                }
            }
        }
        
        $this->setOptions($new);
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
                $details = new ilRadioGroupInputGUI($lng->txt("md_adv_confirm_definition_select_option") . ': "' . $old_option . '"', "conf_det[" . $this->getFieldId() . "][" . $old_option . "]");
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
                foreach ($new_options as $new_option) {
                    $options[$new_option] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
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
                        if (class_implements($class, ilAdvancedMetaDataSubItem)) {
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
                    foreach ($new_options as $new_option) {
                        $options[$new_option] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_option . '"';
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
    
    
    //
    // definition CRUD
    //
    
    public function update()
    {
        parent::update();

        if (is_array($this->confirmed_objects) && count($this->confirmed_objects) > 0) {
            ilADTFactory::initActiveRecordByType();
            foreach ($this->confirmed_objects as $old_option => $item_ids) {
                foreach ($item_ids as $item => $new_option) {
                    $item = explode("_", $item);
                    $obj_id = $item[0];
                    $sub_type = $item[1];
                    $sub_id = $item[2];
                    
                    if (!$new_option) {
                        // remove existing value
                        $primary = array(
                            "obj_id" => array("integer", $obj_id),
                            "sub_type" => array("text", $sub_type),
                            "sub_id" => array("integer", $sub_id),
                            "field_id" => array("integer", $this->getFieldId())
                        );
                        ilADTActiveRecordByType::deleteByPrimary("adv_md_values", $primary, "Enum");
                    } else {
                        // update existing value
                        $primary = array(
                            "obj_id" => array("integer", $obj_id),
                            "sub_type" => array("text", $sub_type),
                            "sub_id" => array("integer", $sub_id),
                            "field_id" => array("integer", $this->getFieldId())
                        );
                        ilADTActiveRecordByType::writeByPrimary("adv_md_values", $primary, "Enum", $new_option);
                    }
                    
                    if ($sub_type == "wpg") {
                        // #15763 - adapt advmd page lists
                        include_once "Modules/Wiki/classes/class.ilPCAMDPageList.php";
                        ilPCAMDPageList::migrateField($obj_id, $this->getFieldId(), $old_option, $new_option);
                    }
                }
            }
        }
    }
    
    
    //
    // export/import
    //
    
    protected function addPropertiesToXML(ilXmlWriter $a_writer)
    {
        foreach ($this->getOptions() as $value) {
            $a_writer->xmlElement('FieldValue', null, $value);
        }
    }
    
    public function importXMLProperty($a_key, $a_value)
    {
        $this->options[] = $a_value;
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
}
