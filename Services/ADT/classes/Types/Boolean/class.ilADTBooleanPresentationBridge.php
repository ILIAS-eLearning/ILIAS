<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTBooleanPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTBoolean);
    }
    
    public function getHTML()
    {
        global $DIC;

        $lng = $DIC['lng'];
                        
        if (!$this->getADT()->isNull()) {
            // :TODO: force icon?
            
            $presentation_value = $this->getADT()->getStatus()
                ? $lng->txt("yes")
                : $lng->txt("no");
            return $this->decorate($presentation_value);
        }
    }
    
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: cast to int ?
            return $this->getADT()->getStatus() ? 1 : 0;
        }
    }
}
