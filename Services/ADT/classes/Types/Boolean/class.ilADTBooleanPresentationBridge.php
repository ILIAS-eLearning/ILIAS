<?php declare(strict_types=1);

class ilADTBooleanPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTBoolean);
    }

    public function getHTML() : string
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (!$this->getADT()->isNull()) {
            // :TODO: force icon?

            $presentation_value = $this->getADT()->getStatus()
                ? $lng->txt("yes")
                : $lng->txt("no");
            return $this->decorate($presentation_value);
        }
    }

    public function getSortable() : mixed
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: cast to int ?
            return $this->getADT()->getStatus() ? 1 : 0;
        }
    }
}
