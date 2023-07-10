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

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilDclFileFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(
        ilPropertyFormGUI $form,
        ?int $record_id = null
    ): ?ilFormPropertyGUI {
        $input = new ilFileInputGUI(
            $this->getField()->getTitle(),
            'field_' . $this->getField()->getId()
        );

        $supported_suffixes = $this->getField()->getSupportedExtensions();
        if (!empty($supported_suffixes)) {
            $input->setSuffixes($supported_suffixes);
        }

        $input->setAllowDeletion(true);
        $this->requiredWorkaroundForInputField($input, $record_id); // TODO CHeck

        return $input;
    }

    private function requiredWorkaroundForInputField(
        ilFileInputGUI $input,
        ?int $record_id
    ): void {
        if ($record_id !== null) {
            $record = ilDclCache::getRecordCache($record_id);
        }

        $this->setupInputField($input, $this->getField());

        //WORKAROUND
        // If field is from type file: if it's required but already has a value it is no longer required as the old value is taken as default without the form knowing about it.
        if ($record_id !== null && $record->getId()) {
            $field_value = $record->getRecordFieldValue((int)$this->getField()->getId());
            if ($field_value) {
                $input->setRequired(false);
            }
        }
        // If this is an ajax request to return the form, input files are currently not supported
        if ($this->ctrl->isAsynch()) {
            $input->setDisabled(true);
        }
    }

    protected function buildFieldCreationInput(
        ilObjDataCollection $dcl,
        string $mode = 'create'
    ): ilRadioOption {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_filetype = new ilTextInputGUI(
            $this->lng->txt('dcl_supported_filetypes'),
            'prop_' . ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES
        );
        $prop_filetype->setInfo($this->lng->txt('dcl_supported_filetypes_desc'));

        $opt->addSubItem($prop_filetype);

        return $opt;
    }
}
