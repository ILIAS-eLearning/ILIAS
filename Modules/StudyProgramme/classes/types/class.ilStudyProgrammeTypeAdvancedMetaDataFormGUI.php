<?php

declare(strict_types=1);

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

/**
 * Class ilStudyProgrammeTypeAdvancedMetaDataFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeAdvancedMetaDataFormGUI extends ilPropertyFormGUI
{
    protected ilStudyProgrammeTypeRepository $type_repository;
    protected ilStudyProgrammeTypeGUI $parent_gui;

    public function __construct(
        ilStudyProgrammeTypeGUI $parent_gui,
        ilStudyProgrammeTypeRepository $type_repository,
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng
    ) {
        $this->parent_gui = $parent_gui;
        $this->type_repository = $type_repository;
        $this->global_tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }

    /**
     * Save object (create or update)
     */
    public function saveObject(ilStudyProgrammeType $type): bool
    {
        $type = $this->fillObject($type);
        if (!$type) {
            return false;
        }
        $this->type_repository->updateType($type);
        return true;
    }

    protected function initForm(): void
    {
        /** @var ilAdvancedMDRecord $record */
        $records = $this->type_repository->getAllAMDRecords();
        $options = array();
        foreach ($records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('prg_type_assign_amd_sets'));

        $item = new ilMultiSelectInputGUI($this->lng->txt('prg_type_available_amd_sets'), 'amd_records');
        $item->setOptions($options);
        $item->setWidth(420);
        $this->addItem($item);
        $this->addCommandButton('updateAMD', $this->lng->txt('save'));
    }

    /**
     * Add all fields to the form
     */
    public function fillForm(ilStudyProgrammeType $type): void
    {
        $records_selected = $this->type_repository->getAssignedAMDRecordIdsByType($type->getId());
        $item = $this->getItemByPostVar('amd_records');
        $item->setValue($records_selected);
    }

    /**
     * Check validity of form and pass values from form to object
     */
    protected function fillObject(ilStudyProgrammeType $type): ?ilStudyProgrammeType
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return null;
        }
        try {
            // Assign and deassign amd records. A plugin could prevent those actions.
            $record_ids_selected = (array) $this->getInput('amd_records');
            $record_ids = $this->type_repository->getAssignedAMDRecordIdsByType($type->getId(), true);
            $record_ids_removed = array_diff($record_ids, $record_ids_selected);
            $record_ids_added = array_diff($record_ids_selected, $record_ids);
            foreach ($record_ids_added as $record_id) {
                $type->assignAdvancedMDRecord($record_id);
            }
            foreach ($record_ids_removed as $record_id) {
                $type->deassignAdvancedMdRecord($record_id);
            }
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage("failure", $e->getMessage());
            return null;
        }
        return $type;
    }
}
