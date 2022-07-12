<?php declare(strict_types=1);

class ilADTIntegerDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTInteger);
    }

    // CRUD

    public function readRecord(array $a_row) : void
    {
        $this->getADT()->setNumber($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields) : void
    {
        $a_fields[$this->getElementId()] = array("integer", $this->getADT()->getNumber());
    }
}
