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
 * Class ilDclDateTimeREpresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ilTextInputGUI
    {
        $input = new ilTextInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setValue($this->lng->txt("dcl_editable_in_table_gui"));
        $input->setDisabled(true);
        $this->setupInputField($input, $this->getField());

        return $input;
    }

    /**
     * @return string|array|null
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_SELECT,
            false, $this->getField()->getId());
        $options = array("" => $this->lng->txt("dcl_any"), 1 => ">1", 2 => ">2", 3 => ">3", 4 => ">4", 5 => "5");
        $input->setOptions($options);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    /**
     * @param int $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter) : bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        if (!$filter || $filter <= $value['avg']) {
            return true;
        }

        return false;
    }
}
