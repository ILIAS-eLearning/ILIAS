<?php declare(strict_types=1);

class ilADTFloatDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTFloat);
    }

    public function readRecord(array $a_row) : void
    {
        $this->getADT()->setNumber($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields) : void
    {
        $a_fields[$this->getElementId()] = array("float", $this->getADT()->getNumber());
    }
}
