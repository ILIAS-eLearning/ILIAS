<?php declare(strict_types=1);
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


class ilADTDateTimeFormBridge extends ilADTFormBridge
{
    protected bool $invalid_input = false;
    protected bool $text_input = false;

    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTDateTime);
    }

    public function setTextInputMode(bool $a_value) : void
    {
        $this->text_input = $a_value;
    }

    public function addToForm() : void
    {
        $adt_date = $this->getADT()->getDate();
        $date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
        $date->setShowTime(true);
        $this->addBasicFieldProperties($date, $this->getADT()->getCopyOfDefinition());
        $date->setDate($adt_date);
        $this->addToParentElement($date);
    }

    public function importFromPost() : void
    {
        $field = $this->getForm()->getItemByPostVar($this->getElementId());

        // because of ilDateTime the ADT can only have valid dates
        if (!$field->hasInvalidInput()) {
            // ilPropertyFormGUI::checkInput() is pre-requisite
            $this->getADT()->setDate($field->getDate());

            $field->setDate($this->getADT()->getDate());
        } else {
            $this->invalid_input = true;
        }
    }

    public function validate() : bool
    {
        // :TODO: error handling is done by ilDateTimeInputGUI
        return !$this->invalid_input;
    }
}
