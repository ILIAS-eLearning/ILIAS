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
 * Class ilOrgUnitTypeAdvancedMetaDataFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypeAdvancedMetaDataFormGUI extends ilPropertyFormGUI
{
    protected ilOrgUnitType $type;
    protected ilObjectGUI $parent_gui;

    public function __construct(ilObjectGUI $parent_gui, ilOrgUnitType $type)
    {
        global $DIC;

        parent::__construct();

        $this->parent_gui = $parent_gui;
        $this->type = $type;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }

    /**
     * Save object (create or update)
     */
    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            return false;
        }

        return true;
    }

    /**
     * Add all fields to the form
     */
    private function initForm(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('orgu_type_assign_amd_sets'));
        $options = array();
        $records = ilOrgUnitType::getAvailableAdvancedMDRecords();
        /** @var ilAdvancedMDRecord $record */
        foreach ($records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }
        $selected = array();
        $records_selected = $this->type->getAssignedAdvancedMDRecordIds();
        foreach ($records_selected as $record_id) {
            $selected[] = $record_id;
        }
        $item = new ilMultiSelectInputGUI($this->lng->txt('orgu_type_available_amd_sets'), 'amd_records');
        $item->setOptions($options);
        $item->setValue($selected);
        $this->addItem($item);
        $this->addCommandButton('updateAMD', $this->lng->txt('save'));
    }

    /**
     * Check validity of form and pass values from form to object
     * @return bool
     */
    private function fillObject(): bool
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return false;
        }
        try {
            // Assign and deassign amd records. A plugin could prevent those actions.
            $record_ids_selected = (array) $this->getInput('amd_records');
            $record_ids = $this->type->getAssignedAdvancedMDRecordIds(true);
            $record_ids_removed = array_diff($record_ids, $record_ids_selected);
            $record_ids_added = array_diff($record_ids_selected, $record_ids);
            foreach ($record_ids_added as $record_id) {
                $this->type->assignAdvancedMDRecord($record_id);
            }
            foreach ($record_ids_removed as $record_id) {
                $this->type->deassignAdvancedMdRecord($record_id);
            }

            return true;
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage('failure', $e->getMessage());

            return false;
        }
    }
}
