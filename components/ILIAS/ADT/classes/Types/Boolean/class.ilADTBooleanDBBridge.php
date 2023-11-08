<?php

declare(strict_types=1);

/**
 * Class ilADTBooleanDBBridge
 */
class ilADTBooleanDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTBoolean);
    }

    // CRUD

    public function readRecord(array $a_row): void
    {
        $this->getADT()->setStatus($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId()] = array("integer", $this->getADT()->getStatus());
    }
}
