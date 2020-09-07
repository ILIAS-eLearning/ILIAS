<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTEnumPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTEnum);
    }
    
    public function getHTML()
    {
        if (!$this->getADT()->isNull()) {
            $options = $this->getADT()->getCopyOfDefinition()->getOptions();
            $value = $this->getADT()->getSelection();
            if (array_key_exists($value, $options)) {
                return $this->decorate($options[$value]);
            }
        }
    }
    
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getSelection();
        }
    }
}
