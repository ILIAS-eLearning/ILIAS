<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTTextPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTText);
    }
    
    public function getHTML()
    {
        if (!$this->getADT()->isNull()) {
            return $this->decorate(nl2br($this->getADT()->getText()));
        }
    }
    
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return strtolower($this->getADT()->getText());
        }
    }
}
