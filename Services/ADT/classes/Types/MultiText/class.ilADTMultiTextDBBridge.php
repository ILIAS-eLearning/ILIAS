<?php declare(strict_types=1);

class ilADTMultiTextDBBridge extends ilADTMultiDBBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }

    protected function readMultiRecord(ilDBStatement $a_set) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $elements = array();

        while ($row = $ilDB->fetchAssoc($a_set)) {
            $elements[] = $row[$this->getElementId()];
        }

        $this->getADT()->setTextElements($elements);
    }

    protected function prepareMultiInsert() : array
    {
        $res = [];
        foreach ((array) $this->getADT()->getTextElements() as $element) {
            $res[] = array($this->getElementId() => array("text", $element));
        }

        return $res;
    }
}
