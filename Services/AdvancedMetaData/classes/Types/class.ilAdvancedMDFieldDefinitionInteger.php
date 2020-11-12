<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
 * AMD field type integer
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionInteger extends ilAdvancedMDFieldDefinition
{
    protected $min; // [integer]
    protected $max; // [integer]
    protected $suffix; // [string]

    protected $suffix_translations = [];
    
    //
    // generic types
    //
    
    public function getType()
    {
        return self::TYPE_INTEGER;
    }
    
    public function isFilterSupported()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getSuffixTranslations()
    {
        return $this->suffix_translations;
    }

    /**
     * @param string $language
     * @param string $suffix
     */
    public function setSuffixTranslation(string $language, string $suffix)
    {
        $this->suffix_translations[$language] = $suffix;
    }

    /**
     * @param array $suffix_translations
     */
    public function setSuffixTranslations(array $suffix_translations)
    {
        $this->suffix_translations = $suffix_translations;
    }

    //
    // ADT
    //

    /**
     * @return ilADTDefinition
     * @throws Exception
     */
    protected function initADTDefinition()
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType('Integer');
        $def->setMin($this->getMin());
        $def->setMax($this->getMax());
        $def->setSuffix(isset($this->getSuffixTranslations()[$this->language]) ?  $this->getSuffixTranslations()[$this->language] : $this->getSuffix());
        return $def;
    }
    
        
    //
    // properties
    //
    
    /**
     * Set min
     *
     * @param int $a_value
     */
    public function setMin($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->min = $a_value;
    }

    /**
     * Get min
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }
    
    /**
     * Set max
     *
     * @param int $a_value
     */
    public function setMax($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->max = $a_value;
    }

    /**
     * Get max
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }
    
    /**
     * Set suffix
     *
     * @param string $a_value
     */
    public function setSuffix($a_value)
    {
        if ($a_value !== null) {
            $a_value = trim($a_value);
        }
        $this->suffix = $a_value;
    }

    /**
     * Get suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
    
    
    //
    // definition (NOT ADT-based)
    //
    
    protected function importFieldDefinition(array $a_def)
    {
        $this->setMin($a_def["min"]);
        $this->setMax($a_def["max"]);
        $this->setSuffix($a_def["suffix"]);
        $this->setSuffixTranslations(isset($a_def['suffix_translations']) ? $a_def['suffix_translations'] : []);
    }
    
    protected function getFieldDefinition()
    {
        return array(
            "min" => $this->getMin(),
            "max" => $this->getMax(),
            "suffix" => $this->getSuffix(),
            'suffix_translations' => $this->getSuffixTranslations()
        );
    }
    
    public function getFieldDefinitionForTableGUI(string $content_language)
    {
        global $DIC;

        $lng = $DIC['lng'];
    
        $res = array();
        
        if ($this->getMin() !== null) {
            $res[$lng->txt("md_adv_number_min")] = $this->getMin();
        }
        if ($this->getMax() !== null) {
            $res[$lng->txt("md_adv_number_max")] = $this->getMax();
        }
        if ($this->getSuffix()) {
            if ($this->useDefaultLanguageMode($content_language)) {
                $suffix = $this->getSuffix();
            } else {
                $suffix = $this->getSuffixTranslations()[$content_language] ?? '';
            }
            $res[$lng->txt("md_adv_number_suffix")] = $suffix;
        }
        
        return $res;
    }

    /**
     * Add input elements to definition form
     * @param ilPropertyFormGUI $a_form
     * @param bool              $a_disabled
     * @param string            $language
     */
    public function addCustomFieldToDefinitionForm(ilPropertyFormGUI $a_form, $a_disabled = false, string $language = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $min = new ilNumberInputGUI($lng->txt("md_adv_number_min"), "min");
        $min->setValue($this->getMin());
        $min->setSize(10);
        $a_form->addItem($min);
        
        $max = new ilNumberInputGUI($lng->txt("md_adv_number_max"), "max");
        $max->setValue($this->getMax());
        $max->setSize(10);
        $a_form->addItem($max);
        
        $suffix = new ilTextInputGUI($lng->txt("md_adv_number_suffix"), "suffix");
        if ($this->useDefaultLanguageMode($language)) {
            $suffix->setValue($this->getSuffix());
        } else {
            $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->record_id)->getDefaultLanguage();
            $suffix->setInfo($default_language . ': ' . $this->getSuffix());
            $suffix->setValue(isset($this->getSuffixTranslations()[$language]) ? $this->getSuffixTranslations()[$language] : '');
        }
        $suffix->setSize(10);
        $a_form->addItem($suffix);
                
        if ($a_disabled) {
            $min->setDisabled(true);
            $max->setDisabled(true);
            $suffix->setDisabled(true);
        }
    }

    /**
     * Import custom post values from definition form
     * @param ilPropertyFormGUI $a_form
     * @param string            $language
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '')
    {
        $min = $a_form->getInput("min");
        $this->setMin(($min !== "") ? $min : null);
        
        $max = $a_form->getInput("max");
        $this->setMax(($max !== "") ? $max : null);

        if ($this->useDefaultLanguageMode($language)) {
            $suffix = $a_form->getInput("suffix");
            $this->setSuffix(($suffix !== "") ? $suffix : null);
        } else {
            $suffix = $a_form->getInput('suffix');
            $this->setSuffixTranslation($language, $suffix);
        }
    }
    
    
    //
    // export/import
    //
    
    protected function addPropertiesToXML(ilXmlWriter $a_writer)
    {
        $a_writer->xmlElement('FieldValue', array("id" => "min"), $this->getMin());
        $a_writer->xmlElement('FieldValue', array("id" => "max"), $this->getMax());
        $a_writer->xmlElement('FieldValue', array("id" => "suffix"), $this->getSuffix());
    }
    
    public function importXMLProperty($a_key, $a_value)
    {
        if ($a_key == "min") {
            $this->setMin($a_value != "" ? $a_value : null);
        }
        if ($a_key == "max") {
            $this->setMax($a_value != "" ? $a_value : null);
        }
        if ($a_key == "suffix") {
            $this->setSuffix($a_value != "" ? $a_value : null);
        }
    }
    
    public function getValueForXML(ilADT $element)
    {
        return $element->getNumber();
    }
    
    public function importValueFromXML($a_cdata)
    {
        $this->getADT()->setNumber($a_cdata);
    }
}
