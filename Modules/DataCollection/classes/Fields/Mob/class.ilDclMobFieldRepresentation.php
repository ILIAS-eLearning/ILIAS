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
class ilDclMobFieldRepresentation extends ilDclFileuploadFieldRepresentation
{
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ilFileInputGUI
    {
        $input = new ilFileInputGUI($this->getField()->getTitle(), 'field_' . $this->getField()->getId());
        $input->setSuffixes(ilDclMobFieldModel::$mob_suffixes);
        $input->setAllowDeletion(true);

        $this->requiredWorkaroundForInputField($input, $record_id);

        return $input;
    }

    /**
     * @return array|string|null
     * @throws Exception
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        $input = $table->addFilterItemByMetaType("filter_" . $this->getField()->getId(), ilTable2GUI::FILTER_TEXT,
            false, $this->getField()->getId());
        $input->setSubmitFormOnEnter(true);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }

    /**
     * @param string $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter) : bool
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());

        $m_obj = new ilObjMediaObject($value);
        $file_name = $m_obj->getTitle();
        if (!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false) {
            return true;
        }

        return false;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create') : ilRadioOption
    {
        $opt = new ilRadioOption($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle()),
            $this->getField()->getDatatypeId());
        $opt->setInfo($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle() . '_desc'));

        $opt->setInfo(sprintf($opt->getInfo(), implode(", ", ilDclMobFieldModel::$mob_suffixes)));

        $prop_width = new ilNumberInputGUI($this->lng->txt('dcl_width'), 'prop_' . ilDclBaseFieldModel::PROP_WIDTH);
        $prop_width->setSize(5);
        $prop_width->setMaxValue(4000);

        $opt->addSubItem($prop_width);

        $prop_height = new ilNumberInputGUI($this->lng->txt('dcl_height'), 'prop_' . ilDclBaseFieldModel::PROP_HEIGHT);
        $prop_height->setSize(5);
        $prop_height->setMaxValue(4000);

        $opt->addSubItem($prop_height);

        $prop_page_details = new ilDclCheckboxInputGUI($this->lng->txt('dcl_link_detail_page'),
            'prop_' . ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
        $opt->addSubItem($prop_page_details);

        return $opt;
    }
}
