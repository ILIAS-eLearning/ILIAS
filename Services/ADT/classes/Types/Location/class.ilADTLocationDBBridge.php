<?php

declare(strict_types=1);

class ilADTLocationDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTLocation);
    }

    public function readRecord(array $a_row): void
    {
        $this->getADT()->setLongitude((float) $a_row[$this->getElementId() . "_long"]);
        $this->getADT()->setLatitude((float) $a_row[$this->getElementId() . "_lat"]);
        $this->getADT()->setZoom($a_row[$this->getElementId() . "_zoom"]);
    }

    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId() . "_long"] = array("float", $this->getADT()->getLongitude());
        $a_fields[$this->getElementId() . "_lat"] = array("float", $this->getADT()->getLatitude());
        $a_fields[$this->getElementId() . "_zoom"] = array("integer", $this->getADT()->getZoom());
    }

    public function supportsDefaultValueColumn(): bool
    {
        return false;
    }
}
