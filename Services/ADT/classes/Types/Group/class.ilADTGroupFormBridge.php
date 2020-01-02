<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTGroupFormBridge extends ilADTFormBridge
{
    protected $elements = []; // [array]
    
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTGroup);
    }
    
    protected function prepareElements()
    {
        if (sizeof($this->elements)) {
            return;
        }
        
        $this->elements = array();
        $factory = ilADTFactory::getInstance();
        
        // convert ADTs to form bridges
        
        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getFormBridgeForInstance($element);
            $this->elements[$name]->setForm($this->getForm());
            $this->elements[$name]->setElementId($name);
        }
    }
    
    public function getElements()
    {
        $this->prepareElements();
        return $this->elements;
    }
    
    public function getElement($a_name)
    {
        $this->prepareElements();
        if (array_key_exists($a_name, $this->elements)) {
            return $this->elements[$a_name];
        }
    }
    
    public function addToForm()
    {
        if ($this->getTitle()) {
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->getTitle());
            
            if ($this->getInfo()) {
                $section->setInfo($this->getInfo());
            }
            
            $this->getForm()->addItem($section);
        }
        
        $this->prepareElements();
        foreach ($this->elements as $element) {
            $element->addToForm();
        }
    }
    
    public function addJS(ilTemplate $a_tpl)
    {
        $this->prepareElements();
        foreach ($this->elements as $element) {
            $element->addJS($a_tpl);
        }
    }
    
    public function importFromPost()
    {
        $this->prepareElements();
        foreach ($this->elements as $element) {
            // parse parent element
            $parent = $element->getParentElement();
            if ($parent) {
                if (is_array($parent)) {
                    $parent = $parent[0];
                }
                if (isset($adt_forms[$parent])) {
                    $parent = $adt_forms[$parent];
                } else {
                    $parent = null;
                }
            }
            if ($element->shouldBeImportedFromPost($parent)) {
                $element->importFromPost();
            }
        }
    }
    
    public function validate()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $valid = true;
        
        // ilADTFormBridge->isRequired() != ilADT->allowNull()
        foreach ($this->getElements() as $element_id => $element) {
            if ($element->isRequired() && $element->getADT()->isNull()) {
                $field = $this->getForm()->getItemByPostvar($element_id);
                $field->setAlert($lng->txt("msg_input_is_required"));
                $valid = false;
            }
            // #17232 - date time input GUI special case
            elseif (!$element->validate()) {
                $valid = false;
            }
        }
                
        if (!$this->getADT()->isValid()) {
            $tmp = array();
            
            // map errors to elements
            
            $mess = $this->getADT()->getValidationErrorsByElements();
            foreach ($mess as $error_code => $element_id) {
                $tmp[$element_id][] = $this->getADT()->translateErrorCode($error_code);
            }
            
            foreach ($tmp as $element_id => $errors) {
                $field = $this->getForm()->getItemByPostvar($element_id);
                $field->setAlert(implode("<br />", $errors));
            }
            
            $valid = false;
        }
        
        return $valid;
    }
}
