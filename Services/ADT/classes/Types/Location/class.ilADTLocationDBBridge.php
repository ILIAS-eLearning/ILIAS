<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTLocationDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTLocation);
    }
    
    
    // CRUD
    
    public function readRecord(array $a_row)
    {
        $this->getADT()->setLongitude($a_row[$this->getElementId() . "_long"]);
        $this->getADT()->setLatitude($a_row[$this->getElementId() . "_lat"]);
        $this->getADT()->setZoom($a_row[$this->getElementId() . "_zoom"]);
    }
    
    public function prepareInsert(array &$a_fields)
    {
        $a_fields[$this->getElementId() . "_long"] = array("float", $this->getADT()->getLongitude());
        $a_fields[$this->getElementId() . "_lat"] = array("float", $this->getADT()->getLatitude());
        $a_fields[$this->getElementId() . "_zoom"] = array("integer", $this->getADT()->getZoom());
    }
}
