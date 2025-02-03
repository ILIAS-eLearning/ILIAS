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

class ilADTMultiTextFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }

    public function addToForm(): void
    {
        $text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $text->setMulti(true);

        $this->addBasicFieldProperties($text, $this->getADT()->getCopyOfDefinition());

        $text->setValue($this->getADT()->getTextElements());

        $this->addToParentElement($text);
    }

    public function importFromPost(): void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setTextElements($this->getForm()->getInput($this->getElementId()));

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue($this->getADT()->getTextElements());
    }
}
