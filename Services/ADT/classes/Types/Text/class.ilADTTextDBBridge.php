<?php

declare(strict_types=1);

class ilADTTextDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTText);
    }

    // CRUD

    public function readRecord(array $a_row): void
    {
        $this->getADT()->setText($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId()] = array("text", $this->getADT()->getText());
    }
}
