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

class ilADTBooleanFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTBoolean);
    }

    public function addToForm(): void
    {
        $bool = new ilCheckboxInputGUI($this->getTitle(), $this->getElementId());
        $bool->setValue('1');
        $this->addBasicFieldProperties($bool, $this->getADT()->getCopyOfDefinition());
        $bool->setRequired(false);
        $bool->setChecked($this->getADT()->getStatus());
        $this->addToParentElement($bool);
    }

    public function importFromPost(): void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $incoming = $this->getForm()->getInput($this->getElementId());

        // unchecked == no incoming
        $incoming = (bool) $incoming;

        $this->getADT()->setStatus($incoming);

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setChecked($this->getADT()->getStatus());
    }

    public function validate(): bool
    {
        return true;
    }

    protected function isActiveForSubItems($a_parent_option = null): bool
    {
        return ($this->getADT()->getStatus() === true);
    }
}
