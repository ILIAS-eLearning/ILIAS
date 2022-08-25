<?php

declare(strict_types=1);
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


class ilADTFloatFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTFloat);
    }

    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());
        $number->setSize(10);
        $number->setDecimals($def->getDecimals());

        $this->addBasicFieldProperties($number, $def);

        $min = $def->getMin();
        if ($min !== null) {
            $number->setMinValue($min);
        }

        $max = $def->getMax();
        if ($max !== null) {
            $number->setMaxValue($max);

            $length = strlen((string) $max) + $def->getDecimals() + 1;
            $number->setSize($length);
            $number->setMaxLength($length);
        }
        $suffix = $def->getSuffix();
        if ($suffix !== null) {
            $number->setSuffix($suffix);
        }
        $number->setValue((string) $this->getADT()->getNumber());
        $this->addToParentElement($number);
    }

    public function importFromPost(): void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setNumber($this->getForm()->getInput($this->getElementId()));
        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue((string) $this->getADT()->getNumber());
    }
}
