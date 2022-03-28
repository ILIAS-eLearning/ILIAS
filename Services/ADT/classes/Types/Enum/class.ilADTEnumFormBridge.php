<?php declare(strict_types=1);

class ilADTEnumFormBridge extends ilADTFormBridge
{
    protected bool $force_radio = false;
    protected array $option_infos = [];
    protected bool $auto_sort = true;

    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTEnum);
    }

    public function setAutoSort($a_value)
    {
        $this->auto_sort = (bool) $a_value;
    }

    public function forceRadio($a_value, array $a_info = null)
    {
        $this->force_radio = (bool) $a_value;
        if ($this->force_radio) {
            $this->option_infos = (array) $a_info;
        }
    }

    public function addToForm() : void
    {
        $def = $this->getADT()->getCopyOfDefinition();
        $selection = $this->getADT()->getSelection();

        $options = $def->getOptions();

        if ($this->auto_sort) {
            asort($options);
        }

        if (!$this->isRequired()) {
            $options = array("" => "-") + $options;
        } elseif ($this->getADT()->isNull()) {
            $options = array("" => $this->lng->txt("please_select")) + $options;
        }

        if (!$this->force_radio) {
            $select = new ilSelectInputGUI($this->getTitle(), $this->getElementId());

            $select->setOptions($options);
        } else {
            $select = new ilRadioGroupInputGUI($this->getTitle(), $this->getElementId());

            foreach ($options as $value => $caption) {
                $option = new ilRadioOption($caption, $value);
                if (is_array($this->option_infos) && array_key_exists($value, $this->option_infos)) {
                    $option->setInfo($this->option_infos[$value]);
                }
                $select->addOption($option);
            }
        }

        $this->addBasicFieldProperties($select, $def);

        $select->setValue($selection);

        $this->addToParentElement($select);
    }

    public function importFromPost() : void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setSelection($this->getForm()->getInput($this->getElementId()));

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue($this->getADT()->getSelection());
    }

    protected function isActiveForSubItems($a_parent_option = null) : bool
    {
        return ($this->getADT()->getSelection() == $a_parent_option);
    }
}
