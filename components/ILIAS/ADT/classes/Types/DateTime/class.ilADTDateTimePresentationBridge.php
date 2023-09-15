<?php

declare(strict_types=1);

class ilADTDateTimePresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTDateTime);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: relative dates?
            return $this->decorate(ilDatePresentation::formatDate($this->getADT()->getDate()));
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return (int) $this->getADT()->getDate()->get(IL_CAL_UNIX);
        }
        return 0;
    }
}
