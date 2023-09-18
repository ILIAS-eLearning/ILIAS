<?php

declare(strict_types=1);

class ilADTTextPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTText);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            return $this->decorate(nl2br($this->getADT()->getText()));
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return strtolower($this->getADT()->getText());
        }
        return '';
    }
}
