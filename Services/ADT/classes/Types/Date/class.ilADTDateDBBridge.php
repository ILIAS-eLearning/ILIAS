<?php

declare(strict_types=1);

class ilADTDateDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTDate);
    }

    // CRUD

    public function readRecord(array $a_row): void
    {
        $date = null;
        if ($a_row[$this->getElementId()]) {
            $date = new ilDate($a_row[$this->getElementId()], IL_CAL_DATE);
        }
        $this->getADT()->setDate($date);
    }

    public function prepareInsert(array &$a_fields): void
    {
        $date = $this->getADT()->getDate();
        if ($date instanceof ilDate) {
            $date = $date->get(IL_CAL_DATE);
        }
        $a_fields[$this->getElementId()] = array("date", $date);
    }
}
