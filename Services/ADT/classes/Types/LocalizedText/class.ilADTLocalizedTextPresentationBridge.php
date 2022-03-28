<?php declare(strict_types=1);
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

    protected function isValidADT(ilADT $a_adt) : bool
    {
        return $a_adt instanceof ilADTLocalizedText;
    }

    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            return $this->decorate(nl2br($this->getADT()->getTextForLanguage($this->lng->getLangKey())));
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return strtolower($this->getADT()->getTextForLanguage($this->lng->getLangKey()));
        }
        return '';
    }
}
