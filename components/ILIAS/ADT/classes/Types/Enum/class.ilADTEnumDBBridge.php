<?php

declare(strict_types=1);

class ilADTEnumDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTEnum);
    }

    public function readRecord(array $a_row): void
    {
        $this->getADT()->setSelection($a_row[$this->getElementId()]);
    }

    public function prepareInsert(array &$a_fields): void
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
    public function supportsDefaultValueColumn(): bool
    {
        return false;
    }
}
