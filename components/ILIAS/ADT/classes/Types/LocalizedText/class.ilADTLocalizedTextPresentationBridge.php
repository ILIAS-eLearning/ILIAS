<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
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
