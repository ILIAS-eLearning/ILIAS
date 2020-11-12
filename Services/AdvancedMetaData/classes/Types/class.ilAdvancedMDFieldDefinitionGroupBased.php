<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
 * AMD field type address
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
abstract class ilAdvancedMDFieldDefinitionGroupBased extends ilAdvancedMDFieldDefinition
{
    protected $options = array();
    protected $complex = array();
    
    
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
        $this->setOptions($a_def["options"]);
        $this->complex = $a_def["complex"];
    }
    
    protected function getFieldDefinition()
    {
        return  array(
            "options" => $this->options,
            "complex" => $this->complex
        );
    }
    
    public function getFieldDefinitionForTableGUI(string $content_language)
    {
        global $lng;
        
        return array($lng->txt("options") => implode(",", $this->getOptions()));
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
        global $lng;
        
        $field = new ilTextInputGUI($lng->txt("options"), "opts");
        $field->setRequired(true);
        $field->setMulti(true);
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
     * Import custom post values from definition form
     * @param ilPropertyFormGUI $a_form
     * @param string            $language
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '')
    {
        $old = $this->getOptions();
        $new = $a_form->getInput("opts");
        
        if (is_array($old)) {
            $missing = array_diff($old, $new);
            if (sizeof($missing)) {
                foreach ($missing as $item) {
                    unset($this->complex[$item]);
                }
            }
        }
        
        $this->setOptions($new);
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
    // complex options
    //
        
    abstract public function getADTGroup();
    
    abstract public function getTitles();
        
    public function hasComplexOptions()
    {
        return true;
    }
            
    protected function getADTForOption($a_option)
    {
        $adt = ilADTFactory::getInstance()->getInstanceByDefinition($this->getADTGroup());
        if (array_key_exists($a_option, $this->complex)) {
            $adt->importStdClass($this->complex[$a_option]);
        }
        return $adt;
    }
    
    // table

    /**
     * @inheritdoc
     */
    public function getComplexOptionsOverview($a_parent_gui, string $a_parent_cmd) : ?string
    {
        include_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionGroupTableGUI.php";
        $tbl = new ilAdvancedMDFieldDefinitionGroupTableGUI($a_parent_gui, $a_parent_cmd, $this);
        return $tbl->getHTML();
    }
        
    public function exportOptionToTableGUI($a_option, array &$a_item)
    {
        $adt = $this->getADTForOption($a_option);
        foreach ($adt->getElements() as $title => $element) {
            $pres = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);
            $a_item[$title] = $pres->getList();
        }
    }
    
    // form
    
    public function initOptionForm(ilPropertyFormGUI $a_form, $a_option_id)
    {
        global $lng;
        
        $option = $this->findOptionById($a_option_id);
        if ($option) {
            $title = new ilTextInputGUI($lng->txt("option"), "option");
            $title->setValue($option);
            $title->setDisabled(true);
            $a_form->addItem($title);
            
            $adt = $this->getADTForOption($option);
            $adt_form = ilADTFactory::getInstance()->getFormBridgeForInstance($adt);
            $adt_form->setForm($a_form);
            
            $titles = $this->getTitles();
            foreach ($adt_form->getElements() as $id => $element) {
                $element->setTitle($titles[$id]);
            }
            
            $adt_form->addToForm();
        }
    }
    
    public function updateComplexOption(ilPropertyFormGUI $a_form, $a_option_id)
    {
        $option = $this->findOptionById($a_option_id);
        if ($option) {
            $adt = ilADTFactory::getInstance()->getInstanceByDefinition($this->getADTGroup());
            $adt_form = ilADTFactory::getInstance()->getFormBridgeForInstance($adt);
            $adt_form->setForm($a_form);
            if ($adt_form->validate()) {
                $adt_form->importFromPost();
                $this->importComplexOptionFromForm($option, $adt);
                return true;
            }
        }
    
        return false;
    }
    
    protected function importComplexOptionFromForm($a_option, ilADT $a_adt)
    {
        $this->complex[$a_option] = $a_adt->exportStdClass();
    }
        
    protected function findOptionById($a_id)
    {
        foreach ($this->getOptions() as $item) {
            if (md5($item) == $a_id) {
                return $item;
            }
        }
    }
}
