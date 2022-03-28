<?php declare(strict_types=1);

class ilADTFloatPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTFloat);
    }

    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            $def = $this->getADT()->getCopyOfDefinition();
            $suffix = $def->getSuffix() ? " " . $def->getSuffix() : null;

            // :TODO: language specific?
            $presentation_value = number_format(
                $this->getADT()->getNumber(),
                $this->getADT()->getCopyOfDefinition()->getDecimals(),
                ",",
                "."
            ) .
                $suffix;

            return $this->decorate($presentation_value);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getNumber();
        }
        return 0;
    }
}
