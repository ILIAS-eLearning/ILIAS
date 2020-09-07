<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTMultiTextDBBridge extends ilADTMultiDBBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTMultiText);
    }
    
    protected function readMultiRecord($a_set)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $elements = array();
        
        while ($row = $ilDB->fetchAssoc($a_set)) {
            $elements[] = $row[$this->getElementId()];
        }
        
        $this->getADT()->setTextElements($elements);
    }
    
    protected function prepareMultiInsert()
    {
        $res = array();
        
        foreach ((array) $this->getADT()->getTextElements() as $element) {
            $res[] = array($this->getElementId() => array("text", $element));
        }
        
        return $res;
    }
}
