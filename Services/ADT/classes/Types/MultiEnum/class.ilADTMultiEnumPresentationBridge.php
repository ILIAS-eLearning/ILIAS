<?php declare(strict_types=1);

class ilADTMultiEnumPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }

    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            $res = array();

            $options = $this->getADT()->getCopyOfDefinition()->getOptions();
            foreach ($this->getADT()->getSelections() as $value) {
                if (array_key_exists($value, $options)) {
                    $res[] = $this->decorate($options[$value]);
                }
            }

            return implode(", ", $res);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return implode(";", $this->getADT()->getSelections());
        }
        return '';
    }
}
