<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTBooleanFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTBoolean);
    }
    
    public function addToForm()
    {
        $bool = new ilCheckboxInputGUI($this->getTitle(), $this->getElementId());
        $bool->setValue(1);
        
        $this->addBasicFieldProperties($bool, $this->getADT()->getCopyOfDefinition());
        
        // :TODO: required checkboxes do not make sense
        $bool->setRequired(false);
        
        $bool->setChecked($this->getADT()->getStatus());
        
        $this->addToParentElement($bool);
    }
        
    public function importFromPost()
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $incoming = $this->getForm()->getInput($this->getElementId());
        
        // unchecked == no incoming
        $incoming = (bool) $incoming;
        
        $this->getADT()->setStatus($incoming);
        
        $field = $this->getForm()->getItemByPostvar($this->getElementId());
        $field->setChecked($this->getADT()->getStatus());
    }
    
    public function validate()
    {
        return true;
    }
    
    protected function isActiveForSubItems($a_parent_option = null)
    {
        return ($this->getADT()->getStatus() === true);
    }
}
