<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTEnumPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTEnum);
    }
    
    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            $options = $this->getADT()->getCopyOfDefinition()->getOptions();
            $value = $this->getADT()->getSelection();
            if (array_key_exists($value, $options)) {
                return $this->decorate($options[$value]);
            }
        }
    }
    
    public function getSortable() : mixed
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getSelection();
        }
    }
}
