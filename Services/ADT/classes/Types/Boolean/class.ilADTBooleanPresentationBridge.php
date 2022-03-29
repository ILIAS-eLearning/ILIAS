<?php declare(strict_types=1);

class ilADTBooleanPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTBoolean);
    }

    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: force icon?

            $presentation_value = $this->getADT()->getStatus()
                ? $this->lng->txt("yes")
                : $this->lng->txt("no");
            return $this->decorate($presentation_value);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: cast to int ?
            return $this->getADT()->getStatus() ? 1 : 0;
        }
        return 0;
    }
}
