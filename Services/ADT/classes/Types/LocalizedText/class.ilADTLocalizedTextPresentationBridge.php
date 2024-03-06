<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilADTLocalizedTextPresentationBridge
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedTextPresentationBridge extends ilADTTextPresentationBridge
{
    public function __construct(ilADT $a_adt)
    {
        parent::__construct($a_adt);
    }

    protected function isValidADT(ilADT $a_adt): bool
    {
        return $a_adt instanceof ilADTLocalizedText;
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            return $this->decorate(nl2br($this->getTextForCurrentLanguageIfAvailable()));
        }
        return '';
    }

    public function getSortable(): string
    {
        if (!$this->getADT()->isNull()) {
            return strtolower($this->getTextForCurrentLanguageIfAvailable());
        }
        return '';
    }

    private function getTextForCurrentLanguageIfAvailable(): string
    {
        $language = $this->lng->getLangKey();
        if (!$this->getADT()->getCopyOfDefinition()->getMultilingualValueSupport()) {
            $language = $this->getADT()->getCopyOfDefinition()->getDefaultLanguage();
        }
        return $this->getADT()->getTextForLanguage($language);
    }
}
