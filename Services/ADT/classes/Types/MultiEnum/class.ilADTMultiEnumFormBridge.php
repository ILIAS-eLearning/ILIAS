<?php

declare(strict_types=1);

class ilADTMultiEnumFormBridge extends ilADTFormBridge
{
    protected ?array $option_infos = [];
    protected bool $auto_sort = true;

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }

    public function setOptionInfos(array $a_info = null): void
    {
        $this->option_infos = $a_info;
    }

    public function setAutoSort(bool $a_value): void
    {
        $this->auto_sort = $a_value;
    }

    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();
        $options = $def->getOptions();

        if ($this->auto_sort) {
            asort($options);
        }

        $cbox = new ilCheckboxGroupInputGUI($this->getTitle(), $this->getElementId());

        foreach ($options as $value => $caption) {
            $option = new ilCheckboxOption($caption, (string) $value);
            if (is_array($this->option_infos) && array_key_exists($value, $this->option_infos)) {
                $option->setInfo($this->option_infos[$value]);
            }
            $cbox->addOption($option);
        }

        $this->addBasicFieldProperties($cbox, $def);

        $cbox->setValue($this->getADT()->getSelections());

        $this->addToParentElement($cbox);
    }

    public function importFromPost(): void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setSelections($this->getForm()->getInput($this->getElementId()));

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue($this->getADT()->getSelections());
    }

    protected function isActiveForSubItems($a_parent_option = null): bool
    {
        $current = $this->getADT()->getSelections();
        return (is_array($current) && in_array($a_parent_option, $current));
    }
}
