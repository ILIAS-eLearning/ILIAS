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

namespace ILIAS\EmployeeTalk\Metadata;

class MetadataHandler implements MetadataHandlerInterface
{
    public function getEditForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        string $form_action,
        string $submit_command,
        string $submit_label
    ): EditFormInterface {
        $md = $this->initMetaData($type, $id, $subtype, $sub_id, \ilAdvancedMDRecordGUI::MODE_EDITOR);
        return new EditForm($md, false, $form_action, $submit_command, $submit_label);
    }

    public function getDisabledEditForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id
    ): EditFormInterface {
        $md = $this->initMetaData($type, $id, $subtype, $sub_id, \ilAdvancedMDRecordGUI::MODE_EDITOR);
        return new EditForm($md, true, '', '', '');
    }

    public function copyValues(
        string $from_type,
        int $from_id,
        string $to_type,
        int $to_id,
        string $subtype
    ): void {
        // assign talk series type to adv md records of the template
        foreach (\ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $from_type,
            $from_id,
            $subtype,
            false
        ) as $rec) {
            if (!$rec->isAssignedObjectType($to_type, $subtype)) {
                $rec->appendAssignedObjectType(
                    $to_type,
                    $subtype,
                    true
                );
                $rec->update();
            }
        }

        \ilAdvancedMDRecord::saveObjRecSelection(
            $to_id,
            $subtype,
            \ilAdvancedMDRecord::getObjRecSelection($from_id, $subtype)
        );

        \ilAdvancedMDValues::_cloneValues(
            0,
            $from_id,
            $to_id,
            $subtype
        );
    }

    public function attachSelectionToForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        \ilPropertyFormGUI $form
    ): void {
        $md = $this->initMetaData($type, $id, $subtype, $sub_id, \ilAdvancedMDRecordGUI::MODE_REC_SELECTION);
        $md->setPropertyForm($form);
        $md->parse();
    }

    public function saveSelectionFromForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        \ilPropertyFormGUI $form
    ): void {
        $md = $this->initMetaData($type, $id, $subtype, $sub_id, \ilAdvancedMDRecordGUI::MODE_REC_SELECTION);
        $md->setPropertyForm($form);
        $md->saveSelection();
    }

    protected function initMetaData(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        int $mode
    ): \ilAdvancedMDRecordGUI {
        $md = new \ilAdvancedMDRecordGUI(
            $mode,
            $type,
            $id,
            $subtype,
            $sub_id,
            false
        );
        return $md;
    }
}
