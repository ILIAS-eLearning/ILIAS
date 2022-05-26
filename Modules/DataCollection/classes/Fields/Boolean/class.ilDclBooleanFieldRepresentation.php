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
 * Class ilDclFileuploadFieldRepresentaion
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBooleanFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ilDclCheckboxInputGUI
    {
        $input = new ilDclCheckboxInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    /**
     * @param ilTable2GUI $table
     * @return array|string|null
     * @throws Exception
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT,
            false, $this->getField()->getId());
        $input->setOptions(
            array(
                "" => $this->lng->txt("dcl_any"),
                "not_checked" => $this->lng->txt("dcl_not_checked"),
                "checked" => $this->lng->txt("dcl_checked"),
            )
        );

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    public function passThroughFilter(ilDclBaseRecordModel $record, $filter) : bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if ((($filter == "checked" && $value == 1) || ($filter == "not_checked" && $value == 0)) || $filter == '' || !$filter) {
            return true;
        }

        return false;
    }
}
