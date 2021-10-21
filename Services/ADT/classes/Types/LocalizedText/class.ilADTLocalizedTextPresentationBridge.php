<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilADTLocalizedTextPresentationBridge
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedTextPresentationBridge extends ilADTTextPresentationBridge
{
    private ilLanguage $lng;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        $this->lng = $DIC->language();
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
    }

    public function getSortable() : mixed
    {
        if (!$this->getADT()->isNull()) {
            return strtolower($this->getADT()->getTextForLanguage($this->lng->getLangKey()));
        }
    }
}