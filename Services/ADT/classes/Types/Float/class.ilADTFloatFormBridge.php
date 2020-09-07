<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTFloatFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTFloat);
    }
    
    public function addToForm()
    {
        $def = $this->getADT()->getCopyOfDefinition();
        
        $number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());
        $number->setSize(10);
        $number->setDecimals($def->getDecimals());
        
        $this->addBasicFieldProperties($number, $def);
        
        $min = $def->getMin();
        if ($min !== null) {
            $number->setMinValue($min);
        }
        
        $max = $def->getMax();
        if ($max !== null) {
            $number->setMaxValue($max);
            
            $length = strlen($max) + $def->getDecimals() + 1;
            $number->setSize($length);
            $number->setMaxLength($length);
        }
        
        $suffix = $def->getSuffix();
        if ($suffix !== null) {
            $number->setSuffix($suffix);
        }
        
        $number->setValue($this->getADT()->getNumber());
        
        $this->addToParentElement($number);
    }
    
    public function importFromPost()
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setNumber($this->getForm()->getInput($this->getElementId()));
        
        $field = $this->getForm()->getItemByPostvar($this->getElementId());
        $field->setValue($this->getADT()->getNumber());
    }
}
