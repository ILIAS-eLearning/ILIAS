<?php

declare(strict_types=1);

/**
 * Class ilADTDateFormBridge
 */
class ilADTDateFormBridge extends ilADTFormBridge
{
    protected bool $invalid_input = false;

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTDate);
    }

    protected function addToElementId($a_add): string
    {
        return $this->getElementId() . "[" . $a_add . "]";
    }

    public function addToForm(): void
    {
        $adt_date = $this->getADT()->getDate();

        $date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
        $date->setShowTime(false);

        $this->addBasicFieldProperties($date, $this->getADT()->getCopyOfDefinition());

        $date->setDate($adt_date);

        $this->addToParentElement($date);
    }

    public function importFromPost(): void
    {
        $field = $this->getForm()->getItemByPostVar($this->getElementId());

        // because of ilDate the ADT can only have valid dates
        if (!$field->hasInvalidInput()) {
            // ilPropertyFormGUI::checkInput() is pre-requisite
            $this->getADT()->setDate($field->getDate());

            $field->setDate($this->getADT()->getDate());
        } else {
            $this->invalid_input = true;
        }
    }

    public function validate(): bool
    {
        // :TODO: error handling is done by ilDateTimeInputGUI
        return !$this->invalid_input;
    }
}
