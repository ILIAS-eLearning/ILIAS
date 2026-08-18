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

use ILIAS\UI\Component\Input\Container\Form\FormInput;

abstract class ilDclSelectionFieldRepresentation extends ilDclBaseFieldRepresentation
{
    /** @var ilDclSelectionFieldModel */
    protected ilDclBaseFieldModel $field;

    public function getInputField(): FormInput
    {
        $options = [];
        foreach (ilDclSelectionOption::getAllForField((int) $this->getField()->getId()) as $opt) {
            $options[$opt->getOptId()] = $this->getField()->personalizeOptionValue($opt->getValue(), $this->user);
        }

        switch ($this->getField()->getProperty($this->field::PROP_SELECTION_TYPE)) {
            case ilDclSelectionFieldModel::SELECTION_TYPE_MULTI:
                return $this->factory->input()->field()->multiSelect(
                    $this->getField()->getTitle(),
                    $options,
                    $this->field->getDescription()
                );
            case ilDclSelectionFieldModel::SELECTION_TYPE_COMBOBOX:
                return $this->factory->input()->field()->select(
                    $this->getField()->getTitle(),
                    $options,
                    $this->field->getDescription()
                );
            case ilDclSelectionFieldModel::SELECTION_TYPE_SINGLE:
            default:
                $input = $this->factory->input()->field()->radio(
                    $this->getField()->getTitle(),
                    $this->getField()->getDescription()
                );
                foreach ($options as $key => $opt) {
                    $input = $input->withOption((string) $key, $opt);
                }
                return $input;
        }
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): mixed
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->getField()->getId()
        );

        $options = ilDclSelectionOption::getAllForField((int) $this->getField()->getId());
        $array = ['' => $this->lng->txt('dcl_all_entries')];
        foreach ($options as $opt) {
            $array[$opt->getOptId()] = $opt->getValue();
        }

        $array['none'] = $this->lng->txt('dcl_no_entry');

        $input->setOptions($array);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $selection_options = $this->buildOptionsInput();
        $opt->addSubItem($selection_options);

        $selection_type = new ilRadioGroupInputGUI(
            $this->lng->txt('dcl_selection_type'),
            'prop_' . $this->field::PROP_SELECTION_TYPE
        );
        $selection_type->setRequired(true);

        $options = [
            ilDclSelectionFieldModel::SELECTION_TYPE_SINGLE,
            ilDclSelectionFieldModel::SELECTION_TYPE_COMBOBOX,
            ilDclSelectionFieldModel::SELECTION_TYPE_MULTI
        ];

        foreach ($options as $option) {
            $selection_type->addOption(new ilRadioOption($this->lng->txt('dcl_' . $option), $option));
        }

        $opt->addSubItem($selection_type);

        $prop_unique = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_unique'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_UNIQUE)
        );
        $prop_unique->setInfo($this->lng->txt('dcl_unique_desc'));
        $opt->addSubItem($prop_unique);

        return $opt;
    }


    abstract protected function buildOptionsInput(): ilDclGenericMultiInputGUI;
}
