<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTDateFormBridge extends ilADTFormBridge
{
    protected $invalid_input; // [bool]
    
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTDate);
    }
    
    protected function addToElementId($a_add)
    {
        return $this->getElementId() . "[" . $a_add . "]";
    }
    
    public function addToForm()
    {
        $adt_date = $this->getADT()->getDate();

        $date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
        $date->setShowTime(false);

        $this->addBasicFieldProperties($date, $this->getADT()->getCopyOfDefinition());
        
        $date->setDate($adt_date);

        $this->addToParentElement($date);
    }
    
    public function importFromPost()
    {
        $field = $this->getForm()->getItemByPostvar($this->getElementId());
        
        // because of ilDate the ADT can only have valid dates
        if (!$field->hasInvalidInput()) {
            // ilPropertyFormGUI::checkInput() is pre-requisite
            $this->getADT()->setDate($field->getDate());

            $field->setDate($this->getADT()->getDate());
        } else {
            $this->invalid_input = true;
        }
    }
    
    public function validate()
    {
        // :TODO: error handling is done by ilDateTimeInputGUI
        return !(bool) $this->invalid_input;
    }
}
