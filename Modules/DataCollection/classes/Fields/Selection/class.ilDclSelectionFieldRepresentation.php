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
 ********************************************************************
 */

/**
 * Class ilDclSelectionFieldRepresentation
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionFieldRepresentation extends ilDclBaseFieldRepresentation
{

    // those should be overwritten by subclasses
    const PROP_SELECTION_TYPE = '';
    const PROP_SELECTION_OPTIONS = '';

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create') : ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $selection_options = $this->buildOptionsInput();
        $opt->addSubItem($selection_options);

        $selection_type = new ilRadioGroupInputGUI($this->lng->txt('dcl_selection_type'),
            'prop_' . static::PROP_SELECTION_TYPE);
        $selection_type->setRequired(true);

        $option_1 = new ilRadioOption(
            $this->lng->txt('dcl_' . ilDclSelectionFieldModel::SELECTION_TYPE_SINGLE),
            ilDclSelectionFieldModel::SELECTION_TYPE_SINGLE
        );
        $selection_type->addOption($option_1);

        $option_2 = new ilRadioOption(
            $this->lng->txt('dcl_' . ilDclSelectionFieldModel::SELECTION_TYPE_MULTI),
            ilDclSelectionFieldModel::SELECTION_TYPE_MULTI
        );
        $selection_type->addOption($option_2);

        $option_3 = new ilRadioOption(
            $this->lng->txt('dcl_' . ilDclSelectionFieldModel::SELECTION_TYPE_COMBOBOX),
            ilDclSelectionFieldModel::SELECTION_TYPE_COMBOBOX
        );
        $selection_type->addOption($option_3);

        $opt->addSubItem($selection_type);

        return $opt;
    }

    /**
     * @return ilMultiSelectInputGUI|ilRadioGroupInputGUI|ilSelectInputGUI
     */
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ilFormPropertyGUI
    {
        /** @var ilDclSelectionOption[] $options */
        $options = ilDclSelectionOption::getAllForField($this->getField()->getId());
        switch ($this->getField()->getProperty(static::PROP_SELECTION_TYPE)) {
            case ilDclSelectionFieldModel::SELECTION_TYPE_SINGLE:
                $input = new ilRadioGroupInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
                foreach ($options as $opt) {
                    $input->addOption(new ilRadioOption($opt->getValue(), $opt->getOptId()));
                }
                $input->setValue(array_keys($options)[0]);
                break;
            case ilDclSelectionFieldModel::SELECTION_TYPE_MULTI:
                $input = new ilMultiSelectInputGUI($this->getField()->getTitle(),
                    'field_' . $this->getField()->getId());

                $input->setHeight(100);
                $input->setHeightUnit('%; max-height: 150px');
                $input->setWidth(100);
                $input->setWidthUnit('%');

                $array = array();
                foreach ($options as $opt) {
                    $array[$opt->getOptId()] = $opt->getValue();
                }
                $input->setOptions($array);
                break;
            case ilDclSelectionFieldModel::SELECTION_TYPE_COMBOBOX:
                $input = new ilSelectInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
                $array = array();
                foreach ($options as $opt) {
                    $array[$opt->getOptId()] = $opt->getValue();
                }
                $input->setOptions(array("" => $this->lng->txt('dcl_please_select')) + $array);
                break;
        }
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    /**
     * @param ilTable2GUI $table
     * @return string|array|null
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT,
            false, $this->getField()->getId());

        $options = ilDclSelectionOption::getAllForField($this->getField()->getId());
        $array = array('' => $this->lng->txt('dcl_all_entries'));
        foreach ($options as $opt) {
            $array[$opt->getOptId()] = $opt->getValue();
        }

        $array['none'] = $this->lng->txt('dcl_no_entry');

        $input->setOptions($array);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    abstract protected function buildOptionsInput() : ilDclGenericMultiInputGUI;
}
