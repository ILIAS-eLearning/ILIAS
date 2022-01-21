<?php declare(strict_types=1);

class ilADTMultiTextFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }

    public function addToForm() : void
    {
        $text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $text->setMulti(true);

        $this->addBasicFieldProperties($text, $this->getADT()->getCopyOfDefinition());

        $text->setValue($this->getADT()->getTextElements());

        $this->addToParentElement($text);
    }

    public function importFromPost() : void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setTextElements($this->getForm()->getInput($this->getElementId()));

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue($this->getADT()->getTextElements());
    }
}
