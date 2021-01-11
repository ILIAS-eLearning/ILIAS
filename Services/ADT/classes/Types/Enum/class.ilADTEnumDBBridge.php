<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTEnumDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTEnum);
    }
    
    public function readRecord(array $a_row)
    {
        $this->getADT()->setSelection($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields)
    {
        $a_fields[$this->getElementId()] = [
            ilDBConstants::T_INTEGER,
            $this->getADT()->getSelection()
        ];
    }

    /**
     * Column is value_index
     * @return bool
     */
    public function supportsDefaultValueColumn() : bool
    {
        return false;
    }
}
