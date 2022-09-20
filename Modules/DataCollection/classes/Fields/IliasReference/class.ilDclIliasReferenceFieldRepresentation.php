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
 * Class ilDclIliasReferenceFieldRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclIliasReferenceFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0): ilRepositorySelector2InputGUI
    {
        $input = new ilRepositorySelector2InputGUI(
            $this->getField()->getTitle(),
            'field_' . $this->getField()->getId(),
            false,
            $form
        );
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    /**
     * @return string|array|null
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->getField()->getId()
        );
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    /**
     * @param string|null $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter): bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        $obj_id = ilObject::_lookupObjId($value);
        if (!$filter || strpos(strtolower(ilObject::_lookupTitle($obj_id)), strtolower($filter)) !== false) {
            return true;
        }

        return false;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_ref_link = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_learning_progress'),
            'prop_' . ilDclBaseFieldModel::PROP_LEARNING_PROGRESS
        );
        $opt->addSubItem($prop_ref_link);

        $prop_multi_select = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_ilias_reference_link'),
            'prop_' . ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK
        );
        $opt->addSubItem($prop_multi_select);

        $prop_multi_select = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_display_action_menu'),
            'prop_' . ilDclBaseFieldModel::PROP_DISPLAY_COPY_LINK_ACTION_MENU
        );
        $opt->addSubItem($prop_multi_select);

        return $opt;
    }
}
