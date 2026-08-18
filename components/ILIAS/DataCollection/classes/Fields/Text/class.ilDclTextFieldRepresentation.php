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

class ilDclTextFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function addFilterInputFieldToTable(ilTable2GUI $table): ?string
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

    public function getInputField(): FormInput
    {
        $length = (int) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_LENGTH);
        $title = $this->getField()->getTitle();
        $byline = $this->getField()->getDescription() . ' ' . sprintf($this->lng->txt('dcl_max_text_length'), $length);
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $input = $this->factory->input()->field()->section(
                [
                    'link' => $this->factory->input()->field()->text($title . $this->lng->txt('dcl_text_suffix_url'), ''),
                    'title' => $this->factory->input()->field()->text($title . $this->lng->txt('dcl_text_suffix_title'), $byline)->withMaxLength($length)
                ],
                ''
            );
        } else {
            if ($length > 200) {
                $input = $this->factory->input()->field()->textarea($title, $byline)->withMaxLimit($length);
            } else {
                $input = $this->factory->input()->field()->text($title, $byline)->withMaxLength($length);
            }
        }

        return $input;
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_length = new ilNumberInputGUI(
            $this->lng->txt('dcl_length'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_LENGTH)
        );
        $prop_length->setSize(5);
        $prop_length->setMinValue(1);
        $prop_length->setMaxValue(4000);
        $prop_length->setRequired(true);
        $prop_length->setValue('200');
        $prop_length->setInfo($this->lng->txt('dcl_length_info'));
        $opt->addSubItem($prop_length);

        $prop_url = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_url'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_URL)
        );
        $opt->addSubItem($prop_url);

        $prop_page_details = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_link_detail_page'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT)
        );
        $prop_page_details->setInfo($this->lng->txt('dcl_link_detail_page_desc'));
        $opt->addSubItem($prop_page_details);

        $prop_unique = new ilDclCheckboxInputGUI(
            $this->lng->txt('dcl_unique'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_UNIQUE)
        );
        $prop_unique->setInfo($this->lng->txt('dcl_unique_desc'));
        $opt->addSubItem($prop_unique);

        $prop_regex = new ilDclTextInputGUI(
            $this->lng->txt('dcl_regex'),
            $this->getPropertyInputFieldId(ilDclBaseFieldModel::PROP_REGEX)
        );
        $prop_regex->setInfo($this->lng->txt('dcl_regex_info'));
        $opt->addSubItem($prop_regex);

        return $opt;
    }
}
