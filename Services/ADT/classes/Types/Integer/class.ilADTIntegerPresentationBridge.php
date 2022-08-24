<?php

declare(strict_types=1);

class ilADTIntegerPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTInteger);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            $def = $this->getADT()->getCopyOfDefinition();
            $suffix = $def->getSuffix() ? " " . $def->getSuffix() : null;

            $presentation_value = $this->getADT()->getNumber() . $suffix;

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
